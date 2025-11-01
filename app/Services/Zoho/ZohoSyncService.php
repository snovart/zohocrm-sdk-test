<?php

namespace App\Services\Zoho;

use App\Models\SyncState;
use App\Services\Zoho\Contracts\ZohoClient;
use App\Models\CrmContact;
use App\Models\CrmAccount;
use App\Models\CrmLead;
use App\Models\CrmDeal;
use App\Services\Zoho\Mappers\ZohoContactMapper;
use App\Services\Zoho\Mappers\ZohoAccountMapper;
use App\Services\Zoho\Mappers\ZohoLeadMapper;
use App\Services\Zoho\Mappers\ZohoDealMapper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ZohoSyncService
{
    public function __construct(private readonly ZohoClient $client) {}

    /**
     * Pull records from Zoho into local DB.
     * Iterates pages/cursor, upserts module rows, updates checkpoints.
     */
    public function pull(string $module, ?string $since = null): int
    {
        $state = $this->getOrCreateState($module);

        // Decide initial cursor or since
        $cursor  = $state->last_cursor ?: null;
        $sinceIso = $since ?: ($state->last_success_at?->toIso8601String());

        $page = 1;
        $totalImported = 0;

        try {
            DB::beginTransaction();

            while (true) {
                $response = $this->client->fetchRecords($module, [
                    'since'    => $sinceIso,
                    'cursor'   => $cursor,
                    'page'     => $page,
                    'per_page' => 200,
                ]);

                $items = $response['data'] ?? [];
                if (empty($items)) {
                    break;
                }

                $this->upsertModule($module, $items);

                $totalImported += count($items);

                // Move to next page/cursor
                $next = $this->client->nextCursor($response);
                if ($next) {
                    $cursor = $next;
                    $page   = 1; // cursor-based paging resets page
                } else {
                    $page++;
                }

                if (($response['has_more'] ?? false) === false && !$next) {
                    break;
                }
            }

            $this->saveCheckpoint($state, $cursor);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $state->last_error_at = Carbon::now();
            $state->save();

            throw $e;
        }

        return $totalImported;
    }

    /** Save sync checkpoint after successful run. */
    public function saveCheckpoint(SyncState $state, ?string $cursor = null): void
    {
        $state->last_success_at = Carbon::now();
        $state->last_cursor     = $cursor;
        $state->save();
    }

    /** Get or create SyncState row for a module. */
    public function getOrCreateState(string $module): SyncState
    {
        return SyncState::firstOrCreate(
            ['module' => $module],
            ['last_success_at' => null, 'last_cursor' => null, 'stats_json' => null, 'last_error_at' => null]
        );
    }

    /**
     * Upsert payload for a specific module.
     * Extensible switch to route to exact handler.
     */
    protected function upsertModule(string $module, array $items): void
    {
        switch (strtolower($module)) {
            case 'contacts':
                $this->upsertContacts($items);
                break;

            case 'accounts':
                $this->upsertAccounts($items);
                break;

            case 'leads':
                $this->upsertLeads($items);
                break;

            case 'deals':
                $this->upsertDeals($items);
                break;

            default:
                // Unknown module name; no-op.
                break;
        }
    }

    /** Upsert Contacts into crm_contacts. */
    protected function upsertContacts(array $items): void
    {
        foreach ($items as $z) {
            $data = ZohoContactMapper::toModelArray((array) $z);
            if (empty($data['zoho_id'])) {
                continue;
            }

            $existing = CrmContact::query()->byZohoId($data['zoho_id'])->first();

            // Skip if local record is newer or same by zoho_modified_at
            if ($existing && $existing->zoho_modified_at && $data['zoho_modified_at']) {
                if ($existing->zoho_modified_at->gte($data['zoho_modified_at'])) {
                    continue;
                }
            }

            CrmContact::updateOrCreate(['zoho_id' => $data['zoho_id']], $data);
        }
    }

    /** Upsert Accounts into crm_accounts. */
    protected function upsertAccounts(array $items): void
    {
        foreach ($items as $z) {
            $data = ZohoAccountMapper::toModelArray((array) $z);
            if (empty($data['zoho_id'])) {
                continue;
            }

            $existing = CrmAccount::query()->byZohoId($data['zoho_id'])->first();

            if ($existing && $existing->zoho_modified_at && $data['zoho_modified_at']) {
                if ($existing->zoho_modified_at->gte($data['zoho_modified_at'])) {
                    continue;
                }
            }

            CrmAccount::updateOrCreate(['zoho_id' => $data['zoho_id']], $data);
        }
    }

    /** Upsert Leads into crm_leads. */
    protected function upsertLeads(array $items): void
    {
        foreach ($items as $z) {
            $data = ZohoLeadMapper::toModelArray((array) $z);
            if (empty($data['zoho_id'])) {
                continue;
            }

            $existing = CrmLead::query()->byZohoId($data['zoho_id'])->first();

            if ($existing && $existing->zoho_modified_at && $data['zoho_modified_at']) {
                if ($existing->zoho_modified_at->gte($data['zoho_modified_at'])) {
                    continue;
                }
            }

            CrmLead::updateOrCreate(['zoho_id' => $data['zoho_id']], $data);
        }
    }

    /** Upsert Deals into crm_deals. */
    protected function upsertDeals(array $items): void
    {
        foreach ($items as $z) {
            $data = ZohoDealMapper::toModelArray((array) $z);
            if (empty($data['zoho_id'])) {
                continue;
            }

            $existing = CrmDeal::query()->byZohoId($data['zoho_id'])->first();

            if ($existing && $existing->zoho_modified_at && $data['zoho_modified_at']) {
                if ($existing->zoho_modified_at->gte($data['zoho_modified_at'])) {
                    continue;
                }
            }

            CrmDeal::updateOrCreate(['zoho_id' => $data['zoho_id']], $data);
        }
    }
}

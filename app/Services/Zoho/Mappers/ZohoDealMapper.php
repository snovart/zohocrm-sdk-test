<?php

namespace App\Services\Zoho\Mappers;

use Carbon\Carbon;

class ZohoDealMapper
{
    /**
     * Map a single Zoho record to local deal payload (crm_deals).
     */
    public static function toModelArray(array $z): array
    {
        // Helper: read ExactKey or its lowercase twin.
        $get = static fn(array $a, string $k) => $a[$k] ?? $a[strtolower($k)] ?? null;

        // Owner and lookups can be nested arrays.
        $owner   = $get($z, 'Owner');
        $account = $get($z, 'Account_Name');
        $contact = $get($z, 'Contact_Name');
        $pipeline = $get($z, 'Pipeline');

        // Prefer human-readable names for lookups when present.
        $accountName = is_array($account) ? ($account['name'] ?? $account['account_name'] ?? null) : $account;
        $contactName = is_array($contact) ? ($contact['name'] ?? $contact['full_name'] ?? null) : $contact;
        $pipelineName = is_array($pipeline) ? ($pipeline['name'] ?? null) : $pipeline;

        return [
            // identifiers
            'zoho_id'          => $get($z, 'id') ?: $get($z, 'Id'),
            'owner_id'         => is_array($owner) ? ($owner['id'] ?? null) : null,

            // basic
            'deal_name'        => $get($z, 'Deal_Name') ?? $get($z, 'Deal Name'),
            'account_name'     => $accountName,
            'contact_name'     => $contactName,

            // pipeline info
            'pipeline'         => $pipelineName,
            'stage'            => $get($z, 'Stage'),
            'amount'           => ($v = $get($z, 'Amount')) !== null ? (float)$v : null,
            'probability'      => ($v = $get($z, 'Probability')) !== null ? (string)$v : null,
            'closing_date'     => ($d = $get($z, 'Closing_Date')) ? Carbon::parse($d)->format('Y-m-d') : null,

            // meta
            'type'             => $get($z, 'Type'),
            'lead_source'      => $get($z, 'Lead_Source'),
            'campaign_source'  => $get($z, 'Campaign_Source'),

            // misc
            'description'      => $get($z, 'Description'),

            // timestamps from Zoho
            'zoho_created_at'  => ($ts = $get($z, 'Created_Time'))  ? Carbon::parse($ts) : null,
            'zoho_modified_at' => ($ts = $get($z, 'Modified_Time')) ? Carbon::parse($ts) : null,

            // raw payload
            'raw_payload'      => $z,
        ];
    }
}

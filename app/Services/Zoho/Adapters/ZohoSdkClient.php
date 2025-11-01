<?php

namespace App\Services\Zoho\Adapters;

use App\Services\Zoho\Contracts\ZohoClient;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * ZohoSdkClient (models-based with verbose logging)
 *
 * Uses Crmoz Zoho SDK model layer (e.g., ZohoCrmSDK\ModelsZoho\ContactZohoModel).
 * Logs requests, raw responses, normalized results, and errors.
 */
class ZohoSdkClient implements ZohoClient
{
    /**
     * Fetch one page of records for a given Zoho module.
     *
     * Supported $params:
     *  - since: ISO8601 string (filter locally by Modified_Time)
     *  - page:  1-based page index
     *  - per_page: page size (defaults to 200)
     */
    public function fetchRecords(string $module, array $params = []): array
    {
        $modelClass = $this->resolveModelClass($module);

        $page     = (int)($params['page'] ?? 1);
        $perPage  = (int)($params['per_page'] ?? 200);
        $sinceIso = $params['since'] ?? null;

        $options = [
            'page'      => $page,
            'per_page'  => $perPage,
            'since'     => $sinceIso,
            'converted' => 'false',
            'sortOrder' => 'asc',
            'sortBy'    => 'Modified_Time',
        ];

        $debug = (bool)(config('app.debug') || env('ZCRM_DEBUG', false));
        if ($debug) {
            Log::debug('[ZohoSdkClient] fetchRecords(): start', [
                'module'     => $module,
                'modelClass' => $modelClass,
                'params'     => $params,
                'options'    => $options,
            ]);
        }

        try {
            // Use incremental endpoint via models layer.
            // sync(Carbon $since, int $page = 1, bool $withSubforms = false, $perPage = false,
            //      string $converted = 'false', $sortOrder = 'desc', $sortBy = 'Modified_Time')
            $sinceCarbon = $sinceIso ? \Carbon\Carbon::parse($sinceIso) : \Carbon\Carbon::createFromTimestamp(0);

            $collection = $modelClass::sync(
                $sinceCarbon,
                $page,
                false,
                $perPage,
                'false',
                'asc',
                'Modified_Time'
            );

            // Extract array from SDK collection of ObjectModel instances.
            // The collection is keyed by Zoho IDs; values are ObjectModel.
            // Convert each ObjectModel to plain array via getAllAttributes().
            $raw = [];
            if (is_array($collection)) {
                $raw = $collection;
            } elseif (method_exists($collection, 'all')) {
                // If the SDK exposes ->all() like Illuminate\Support\Collection
                $raw = $collection->all();
            } elseif (method_exists($collection, 'toArray')) {
                $raw = $collection->toArray();
            } else {
                // Fallback: cast
                $raw = (array) $collection;
            }

            if ($debug) {
                Log::debug('[ZohoSdkClient] fetchRecords(): raw response', [
                    'raw_type' => is_object($collection) ? get_class($collection) : gettype($collection),
                    'raw_keys' => is_array($raw) ? array_slice(array_keys($raw), 0, 5) : [],
                ]);
            }

            // Convert ObjectModel instances to associative arrays.
            $records = [];
            foreach ((array) $raw as $item) {
                if (is_object($item) && method_exists($item, 'getAllAttributes')) {
                    /** @var mixed $item */
                    $records[] = $item->getAllAttributes();
                } elseif (is_array($item)) {
                    $records[] = $item;
                }
            }

            // Optional local filter by Modified_Time when "since" is provided.
            if ($sinceIso) {
                $sinceTs = strtotime($sinceIso);
                $records = array_values(array_filter(
                    $records,
                    static function (array $row) use ($sinceTs): bool {
                        $key = array_key_exists('modified_time', $row) ? 'modified_time'
                            : (array_key_exists('Modified_Time', $row) ? 'Modified_Time' : null);
                        if (!$key || empty($row[$key])) {
                            return true;
                        }
                        return strtotime((string)$row[$key]) >= $sinceTs;
                    }
                ));
            }

            // The SDK collection does not provide "info"; determine has_more heuristically.
            $result = [
                'data'     => $records,
                'info'     => [],
                'has_more' => count($records) >= $perPage,
            ];

            if ($debug) {
                Log::debug('[ZohoSdkClient] fetchRecords(): normalized result', [
                    'count'   => count($records),
                    'hasMore' => $result['has_more'],
                ]);
            }

            return $result;
        } catch (Throwable $e) {
            Log::error('[ZohoSdkClient] fetchRecords(): exception', [
                'module'     => $module,
                'modelClass' => $modelClass,
                'params'     => $params,
                'options'    => $options,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Page-based listing only; no cursor token is provided by the SDK.
     */
    public function nextCursor(array $response): ?string
    {
        return null;
    }

    /**
     * Map Zoho module name to SDK model class with safe fallbacks.
     */
    private function resolveModelClass(string $module): string
    {
        // Primary map (что ожидаем в норме)
        $primary = [
            'Contacts' => \ZohoCrmSDK\ModelsZoho\ContactZohoModel::class,
            'Accounts' => \ZohoCrmSDK\ModelsZoho\AccountZohoModel::class,
            'Leads'    => \ZohoCrmSDK\ModelsZoho\LeadZohoModel::class,
            'Deals'    => \ZohoCrmSDK\ModelsZoho\DealZohoModel::class,
        ];

        if ($module === 'Deals') {
            // Фолбэк для старых/других сборок SDK
            $candidates = [
                \ZohoCrmSDK\ModelsZoho\DealZohoModel::class,
                \ZohoCrmSDK\ModelsZoho\PotentialZohoModel::class,
            ];
            foreach ($candidates as $class) {
                if (class_exists($class)) {
                    return $class;
                }
            }
            throw new \RuntimeException(
                "SDK model for module 'Deals' not found. Tried: " .
                implode(', ', $candidates)
            );
        }

        if (!isset($primary[$module])) {
            throw new InvalidArgumentException("Unsupported module '{$module}'. Update model map.");
        }

        $class = $primary[$module];
        if (!class_exists($class)) {
            throw new \RuntimeException("SDK model class '{$class}' not found for module '{$module}'.");
        }

        return $class;
    }
}

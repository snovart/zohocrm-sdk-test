<?php

namespace App\Services\Zoho\Mappers;

use Carbon\Carbon;

class ZohoContactMapper
{
    /**
     * Map a single Zoho record to local contact payload.
     */
    public static function toModelArray(array $z): array
    {
        // Some SDK payloads may use lowercase keys when accessed via models.
        $get = static fn(array $a, string $k) => $a[$k] ?? $a[strtolower($k)] ?? null;

        return [
            'zoho_id'          => $get($z, 'id') ?: $get($z, 'Id'),
            'owner_id'         => $get($z, 'Owner')['id'] ?? null,
            'first_name'       => $get($z, 'First_Name'),
            'last_name'        => $get($z, 'Last_Name'),
            'full_name'        => $get($z, 'Full_Name'),
            'email'            => $get($z, 'Email'),
            'phone'            => $get($z, 'Phone'),
            'mobile'           => $get($z, 'Mobile'),
            'status'           => $get($z, 'Lead_Source') ?? $get($z, 'Status') ?? null,
            'zoho_created_at'  => ($ts = $get($z, 'Created_Time'))  ? Carbon::parse($ts) : null,
            'zoho_modified_at' => ($ts = $get($z, 'Modified_Time')) ? Carbon::parse($ts) : null,
            'raw_payload'      => $z,
        ];
    }
}

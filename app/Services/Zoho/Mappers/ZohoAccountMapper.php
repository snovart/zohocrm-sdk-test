<?php

namespace App\Services\Zoho\Mappers;

use Carbon\Carbon;

class ZohoAccountMapper
{
    /**
     * Map a single Zoho record to local account payload (crm_accounts).
     */
    public static function toModelArray(array $z): array
    {
        // Helper to read either ExactKey or lowercase key from SDK arrays.
        $get = static fn(array $a, string $k) => $a[$k] ?? $a[strtolower($k)] ?? null;

        // Owner can be an object-like array; keep it safe.
        $owner = $get($z, 'Owner');

        return [
            // identifiers
            'zoho_id'          => $get($z, 'id') ?: $get($z, 'Id'),
            'owner_id'         => is_array($owner) ? ($owner['id'] ?? null) : null,

            // core
            'account_name'     => $get($z, 'Account_Name'),
            'website'          => $get($z, 'Website'),
            'phone'            => $get($z, 'Phone'),
            'fax'              => $get($z, 'Fax'),
            'email'            => $get($z, 'Email'),

            // company info
            'industry'         => $get($z, 'Industry'),
            'annual_revenue'   => ($v = $get($z, 'Annual_Revenue')) !== null ? (float)$v : null,
            'employees'        => ($v = $get($z, 'Employees')) !== null ? (int)$v : null,
            'ownership'        => $get($z, 'Ownership'),
            'account_type'     => $get($z, 'Account_Type'),
            'rating'           => $get($z, 'Rating'),

            // billing address
            'billing_street'   => $get($z, 'Billing_Street'),
            'billing_city'     => $get($z, 'Billing_City'),
            'billing_state'    => $get($z, 'Billing_State'),
            'billing_zip'      => $get($z, 'Billing_Code'),
            'billing_country'  => $get($z, 'Billing_Country'),

            // shipping address
            'shipping_street'  => $get($z, 'Shipping_Street'),
            'shipping_city'    => $get($z, 'Shipping_City'),
            'shipping_state'   => $get($z, 'Shipping_State'),
            'shipping_zip'     => $get($z, 'Shipping_Code'),
            'shipping_country' => $get($z, 'Shipping_Country'),

            // misc
            'description'      => $get($z, 'Description'),

            // timestamps from Zoho
            'zoho_created_at'  => ($ts = $get($z, 'Created_Time'))  ? Carbon::parse($ts) : null,
            'zoho_modified_at' => ($ts = $get($z, 'Modified_Time')) ? Carbon::parse($ts) : null,

            // raw
            'raw_payload'      => $z,
        ];
    }
}

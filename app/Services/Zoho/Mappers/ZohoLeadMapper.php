<?php

namespace App\Services\Zoho\Mappers;

use Carbon\Carbon;

class ZohoLeadMapper
{
    /**
     * Map a single Zoho record to local lead payload (crm_leads).
     */
    public static function toModelArray(array $z): array
    {
        // Helper to read either ExactKey or lowercase key from SDK arrays.
        $get = static fn(array $a, string $k) => $a[$k] ?? $a[strtolower($k)] ?? null;

        $owner = $get($z, 'Owner');

        return [
            // identifiers
            'zoho_id'          => $get($z, 'id') ?: $get($z, 'Id'),
            'owner_id'         => is_array($owner) ? ($owner['id'] ?? null) : null,

            // identity
            'first_name'       => $get($z, 'First_Name'),
            'last_name'        => $get($z, 'Last_Name'),
            'full_name'        => $get($z, 'Full_Name'),
            'company'          => $get($z, 'Company'),

            // contacts
            'email'            => $get($z, 'Email'),
            'phone'            => $get($z, 'Phone'),
            'mobile'           => $get($z, 'Mobile'),
            'website'          => $get($z, 'Website'),

            // classification
            'lead_source'      => $get($z, 'Lead_Source'),
            'lead_status'      => $get($z, 'Lead_Status'),
            'industry'         => $get($z, 'Industry'),
            'rating'           => $get($z, 'Rating'),

            // money-related
            'annual_revenue'   => ($v = $get($z, 'Annual_Revenue')) !== null ? (float)$v : null,

            // address (flat)
            'street'           => $get($z, 'Street'),
            'city'             => $get($z, 'City'),
            'state'            => $get($z, 'State'),
            'zip'              => $get($z, 'Zip_Code') ?? $get($z, 'Zip'),
            'country'          => $get($z, 'Country'),

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

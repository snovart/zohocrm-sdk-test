<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CrmDeal extends Model
{
    // Table name
    protected $table = 'crm_deals';

    // Mass assignable fields
    protected $fillable = [
        'zoho_id',
        'owner_id',
        'deal_name',
        'account_name',
        'contact_name',
        'pipeline',
        'stage',
        'amount',
        'probability',
        'closing_date',
        'type',
        'lead_source',
        'campaign_source',
        'description',
        'zoho_created_at',
        'zoho_modified_at',
        'raw_payload',
    ];

    // Attribute casting
    protected $casts = [
        'amount'           => 'float',
        'closing_date'     => 'date',
        'zoho_created_at'  => 'datetime',
        'zoho_modified_at' => 'datetime',
        'raw_payload'      => 'array',
    ];

    // Query scope: find by Zoho id
    public function scopeByZohoId(Builder $q, string $zohoId): Builder
    {
        return $q->where('zoho_id', $zohoId);
    }
}

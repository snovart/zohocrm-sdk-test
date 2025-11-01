<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CrmLead extends Model
{
    // Table name
    protected $table = 'crm_leads';

    // Mass assignable fields
    protected $fillable = [
        'zoho_id',
        'owner_id',
        'company',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'lead_status',
        'lead_source',
        'industry',
        'rating',
        'annual_revenue',
        'no_of_employees',
        'description',
        'zoho_created_at',
        'zoho_modified_at',
        'raw_payload',
    ];

    // Attribute casting
    protected $casts = [
        'annual_revenue'   => 'float',
        'no_of_employees'  => 'integer',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CrmAccount extends Model
{
    // Table name
    protected $table = 'crm_accounts';

    // Mass assignable fields
    protected $fillable = [
        'zoho_id',
        'owner_id',
        'account_name',
        'website',
        'phone',
        'industry',
        'account_type',
        'rating',
        'description',
        'zoho_created_at',
        'zoho_modified_at',
        'raw_payload',
    ];

    // Attribute casting
    protected $casts = [
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncState extends Model
{
    // Table name
    protected $table = 'sync_states';

    // Mass assignable fields
    protected $fillable = [
        'module',
        'last_success_at',
        'last_cursor',
        'stats_json',
        'last_error_at',
    ];

    // Cast attributes
    protected $casts = [
        'last_success_at' => 'datetime',
        'last_error_at' => 'datetime',
        'stats_json' => 'array',
    ];
}

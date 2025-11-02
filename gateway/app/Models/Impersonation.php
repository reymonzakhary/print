<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Impersonation extends Model
{
    use HasUuids;

    protected $fillable = [
        'initiator_id', 'target_tenant_id', 'email', 'supplier_name', 'meta', 'expires_at', 'used'
    ];
    protected $connection = 'system';

    protected $casts = [
        'meta' => 'array',
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];
}


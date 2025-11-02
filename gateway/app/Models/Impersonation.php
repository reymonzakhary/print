<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Impersonation extends Model
{
    use HasUuids, UsesSystemConnection;

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


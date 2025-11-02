<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use UsesTenantConnection, SoftDeletes, HasFactory;

    protected $casts = [
        'activity' => AsArrayObject::class
    ];

    public $fillable = ['title', 'activity', 'product', 'type', 'user_id'];
}

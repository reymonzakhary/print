<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{


    protected $fillable = [
        'name', 'slug', 'action', 'ns'
    ];
}

<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'value', 'ctx_id'
    ];
}

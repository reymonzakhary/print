<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Vat extends Model
{
    protected $fillable = ['percentage'];
    public $timestamps = false;

    public function order()
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

class SupplierSharedCategory extends Model
{
    use UsesSystemConnection;

    protected $fillable = [
        'name', 'description', 'category_id'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}

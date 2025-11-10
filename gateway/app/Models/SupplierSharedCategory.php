<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierSharedCategory extends Model
{


    protected $fillable = [
        'name', 'description', 'category_id'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}

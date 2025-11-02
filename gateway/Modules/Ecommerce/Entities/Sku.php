<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Ecommerce\Database\factories\SkuFactory;

class Sku extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory()
    {
        return SkuFactory::new();
    }
}

<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeliveryDay extends Model
{
    use HasFactory;

    protected $fillable = ["label", 'days', 'iso', 'base_id', 'row_id', 'mode', 'price'];

    public function getRouteKeyName()
    {
        return "slug";
    }

    public function setLabelAttribute($value)
    {
        $this->attributes['label'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }
}

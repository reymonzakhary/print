<?php

namespace App\Models;

use App\Models\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Supplier extends Model
{
    use InteractsWithMedia;

    protected $fillable = [
        'supplier_id', 'name', 'share_products', 'hostname_id', 'config'
    ];

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'supplier_id';
    }

    /**
     *
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->registerMediaConversions(function (Media $media) {

            });
    }

    /**
     * @return HasMany
     */
    public function sharedCategories()
    {
        return $this->hasMany(SupplierSharedCategory::class);
    }

    /**
     * @return HasMany
     */
    public function getConfigAttribute()
    {
        return json_decode($this->attributes['config']);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'contracts');
    }
}

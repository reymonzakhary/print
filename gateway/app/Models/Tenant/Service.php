<?php

namespace App\Models\Tenant;

use App\Models\Traits\HasPrice;
use App\Models\Traits\InteractsWithMedia;
use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use InteractsWithMedia, HasPrice, Slugable;

    protected $fillable = ['name', 'description', 'file', 'price', 'discount_id', 'vat'];

    /**
     *
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('order-service')
            ->registerMediaConversions(function (Media $media) {

            });

        $this->addMediaCollection('item-service')
            ->registerMediaConversions(function (Media $media) {

            });
    }
}

<?php

namespace App\Models\Traits;

use App\Services\Geolocation\GoogleGeo;
use Illuminate\Validation\ValidationException;

trait HasGeometry
{
    /**
     * get geometry
     * @throws ValidationException
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $result = GoogleGeo::obtainAddress("{$model->address} {$model->number}, {$model->zip_code} {$model->state} {$model->city} {$model->country->name}");
            if(optional($result)['results'] && optional($result)['status'] === 'OK') {
                $model->lat = optional(optional($result['results'][0])['geometry'])['location']['lat'];
                $model->lng = optional(optional($result['results'][0])['geometry'])['location']['lng'];
            }
        });

    }
}

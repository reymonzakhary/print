<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Hash;

trait HashPassword
{

    /**
     *
     */
    public static function booted()
    {
        static::creating(function (self $model) {
            $model->password = Hash::make($model->password);
        });
    }
}

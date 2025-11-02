<?php

namespace App\Models\Tenants\Media;

use Illuminate\Database\Eloquent\Model;

class MediaSourceRule extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'media_source_id',
        'disk',
        'path',
        'access'
    ];

    public function getPathAttribute($value)
    {
        return str_replace(request()->tenant->uuid . '/', '', $value);
    }
}

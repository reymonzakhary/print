<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

//use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class Media extends Model
{
    use UsesSystemConnection;
}

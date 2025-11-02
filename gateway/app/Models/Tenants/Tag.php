<?php

namespace App\Models\Tenants;

use App\Models\Tenants\Media\FileManager;
use App\Models\Traits\CanBeScoped;
use App\Models\Traits\Slugable;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{

    use UsesTenantConnection, Slugable, CanBeScoped;

    protected $fillable = ['name', 'iso', 'row_id', 'hex'];

    /**
     *
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('iso', app()->getLocale())->first() ?? abort(404, __('Not Found -- There is no tags found'));
    }

    /**
     * return Morph
     */
    public function items()
    {
        return $this->morphedByMany(
            Item::class, 'taggable', 'taggables',
            'tag_id', 'taggable_id', 'row_id'
        );
    }

    public function FileManager()
    {
        return $this->morphedByMany(
            FileManager::class, 'taggable', 'taggables',
            'tag_id', 'taggable_id', 'row_id'
        );
    }
}

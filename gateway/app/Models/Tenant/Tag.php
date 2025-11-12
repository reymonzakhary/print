<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Media\FileManager;
use App\Models\Traits\CanBeScoped;
use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{

    use Slugable, CanBeScoped;

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

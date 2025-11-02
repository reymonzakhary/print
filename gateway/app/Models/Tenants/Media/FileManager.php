<?php

namespace App\Models\Tenants\Media;

use App\Models\Tenants\Tag;
use App\Models\Traits\CanBeScoped;
use App\Models\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class FileManager extends Model
{
    // use UsesTenantConnection, // Removed
        InteractsWithMedia, CanBeScoped;

    protected $table = 'file_manager';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'group',
        'ext',
        'type',
        'path',
        'disk',
        'model_type',
        'model_id',
        'showing_columns',
        'size',
        'collection',
        'external'
    ];

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * @return string
     */
    public function getFullyQualifiedPath(): string
    {
        return !empty($this->getAttribute('path')) ?
            $this->getAttribute('path') . '/' . $this->getAttribute('name') :
            $this->getAttribute('name');
    }
}

<?php

namespace Modules\Cms\Entities;

use App\Models\Traits\CanBeScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\SortableTrait;

class Template extends Model
{
    use SortableTrait, CanBeScoped;


    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'description',
        'folder_id',
        'icon',
        'content',
        'locked',
        'properties',
        'static',
        'path',
    ];

    /**
     * @return BelongsTo
     */
    public function folders()
    {
        return $this->belongsTo(Folder::class, 'folder_id', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function chunks()
    {
        return $this->belongsToMany(Chunk::class, 'template_chunk');
    }

    /**
     * @return HasMany
     */
    public function variables()
    {
        return $this->hasMany(TemplateVariable::class);
    }

    /**
     * @return HasMany
     */
    public function resources()
    {
        return $this->hasMany(Resource::class);
    }
}

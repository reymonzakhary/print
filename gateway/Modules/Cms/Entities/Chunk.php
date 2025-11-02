<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\SortableTrait;

class Chunk extends Model
{
    use SortableTrait;

    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'name', 'content', 'path', 'sort', 'folder_id'
    ];

    /**
     * @return BelongsToMany
     */
    public function templates()
    {
        return $this->belongsToMany(Template::class, 'template_chunk');
    }

    /**
     * @return BelongsTo
     */
    public function folders()
    {
        return $this->belongsTo(Folder::class, 'folder_id', 'id');
    }
}

<?php

namespace Modules\Cms\Entities;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\SortableTrait;

class Snippet extends Model
{
    use UsesTenantConnection, SortableTrait;

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
     * @return BelongsTo
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
}

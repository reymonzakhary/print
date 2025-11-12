<?php

namespace App\Models\Tenant;

use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Area extends Model implements Sortable
{
    use Slugable, SortableTrait;

    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'name', 'slug', 'sort', 'icon'
    ];
}

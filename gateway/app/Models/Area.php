<?php

namespace App\Models;

use App\Models\Traits\Slugable;
use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Area extends Model implements Sortable
{
    use UsesSystemConnection, Slugable, SortableTrait;

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

    public function namespaces()
    {
        return $this->belongsToMany(Npace::class, 'namespace_areas', 'namespace_id', 'area_id');
    }
}

<?php

namespace App\Models;

use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\SortableTrait;

class Npace extends Model
{
    use Slugable, SortableTrait;

    protected $table = 'namespaces';

    protected $fillable = [
        'name', 'slug', 'sort', 'icon'
    ];

    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    /**
     * @return BelongsToMany
     */
    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'namespace_areas', 'namespace_id', 'area_id');
    }
}

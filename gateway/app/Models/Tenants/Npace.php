<?php

namespace App\Models\Tenants;

use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Npace extends Model implements Sortable
{
    use Slugable, SortableTrait;

    protected $table = 'namespaces';

    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'name', 'slug', 'sort', 'icon', 'disabled'
    ];

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'namespace', 'slug');
    }
}

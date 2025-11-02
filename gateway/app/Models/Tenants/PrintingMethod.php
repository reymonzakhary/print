<?php

namespace App\Models\Tenants;

use App\Models\Traits\Slugable;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class PrintingMethod extends Model implements Sortable
{
    use  UsesTenantConnection, Slugable, SortableTrait, SortableTrait;

    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];


    protected $fillable = [
        'name', 'iso', 'slug', 'row_id'
    ];

}

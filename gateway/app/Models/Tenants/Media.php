<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * Class Media
 * @package App\Models\Tenants
 */
class Media extends Model implements Sortable
{
    use UsesTenantConnection, SortableTrait;

    /**
     * @var string
     */
    protected $table = 'media';

    /**
     * @var string[]
     */
    protected $cases = [
        'manipulations' => AsArrayObject::class
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
     * @var string[]
     */
    protected $fillable = [
        'sort',
        'model_type',
        'model_id',
        'user_id',
        'uuid',
        'collection',
        'file_manager_id',
        'size',
        'manipulations',
        'custom_properties',
    ];


    /**
     *
     */
    public static function boot(): void
    {
        static::creating(function ($model) {
            $model->uuid = Str::UUID();
        });
    }
}

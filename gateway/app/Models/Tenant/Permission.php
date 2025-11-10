<?php

namespace App\Models\Tenant;

use App\Models\Traits\CanBeScoped;
use App\Models\Traits\HasAdvancedFilter;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laratrust\Models\Permission as PermissionModel;

class Permission extends PermissionModel
{
    use HasAdvancedFilter, CanBeScoped;

    public $guarded = [];

    protected $fillable = [
        'namespace', 'area', 'name',
        'display_name', 'description',
        'created_at',
        'updated_at',
        'deleted_at',
        'display_name',
        'description',
    ];

    protected $orderable = [
        'id',
        'name',
    ];

    protected $filterable = [
        'id',
        'name',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * @return BelongsTo
     */
    public function namespaces()
    {
        return $this->belongsTo(Npace::class, 'namespace', 'slug');
    }

}

<?php

namespace App\Models;

use DateTimeInterface;
use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laratrust\Models\Permission as PermissionModel;

class Permission extends PermissionModel
{
    use HasFactory, UsesSystemConnection;

    public $guarded = [];

    protected $fillable = [
        'namespace', 'area', 'name',
        'created_at',
        'updated_at',
        'deleted_at',
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


    /**
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(
        DateTimeInterface $date
    ): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * @return BelongsTo
     */
    public function namespaces(): BelongsTo
    {
        return $this->belongsTo(Npace::class, 'namespace', 'slug');
    }
}

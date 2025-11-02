<?php

namespace App\Models\Tenants;

use App\Enums\QueueProcessStatus;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Queue extends Model
{
    use HasFactory, UsesTenantConnection;

    /**
     * @var string[]
     */
    protected $fillable = [
        'signature', 'blueprint_id', 'queueable_type', 'queueable_id',
        'busy', 'output', 'started', 'process', 'st'
    ];

    protected $casts = [
        'output' => AsArrayObject::class,
        'process' => QueueProcessStatus::class
    ];


    /**
     * boot class
     */
    public static function booted()
    {
        static::creating(function ($model) {
            $model->signature = $model->signature ?? (string)Str::uuid();
        });
    }

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(QueueItem::class);
    }


    public function blueprint()
    {
        return $this->belongsTo(Blueprint::class);
    }

    /**
     * @return BelongsTo
     */
    public function sku()
    {
        return $this->belongsTo(Sku::class, 'queueable_id', 'id');
    }


}

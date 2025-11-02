<?php

namespace App\Models\Tenants;

use App\Foundation\FileManager\Traits\InteractWithMedia;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cart extends Model
{
    use UsesTenantConnection, HasFactory, InteractWithMedia;

    protected $fillable = [
        'user_id'
    ];

    protected $casts = [
        'variation' => AsArrayObject::class
    ];


    /**
     * boot class
     */
    public static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }

    /**
     * @return HasMany
     */
    public function cartVariations(): HasMany
    {
        return $this->hasMany(CartVariation::class);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany
     */
    public function variations(): BelongsToMany
    {
        return $this->belongsToMany(Variation::class)
            ->withPivot('qty', 'variation', 'product_id')->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function skus(): BelongsToMany
    {
        return $this->belongsToMany(Sku::class, 'cart_variation')
            ->withPivot('id', 'qty', 'variation', 'product_id', 'price')->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('cart')
            ->acceptsMimeTypes(['image/jpeg'])
            ->useDisk('carts');
    }
}

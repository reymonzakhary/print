<?php

namespace App\Models\Tenants;

use App\Foundation\FileManager\Traits\InteractWithMedia;
use App\Models\Traits\HasPrice;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\Tenants\Media\FileManager;

class CartVariation extends Model
{

    use HasFactory,
        UsesTenantConnection,
        InteractWithMedia,
        HasPrice;

    /**
     * @var string[]
     */
    protected $casts = [
        'variation' => AsArrayObject::class
    ];

    /**
     * @var string
     */
    protected $table = 'cart_variation';

    /**
     * @var string[]
     */
    protected $fillable = ['product_id', "variation", 'qty', 'price', 'reference', 'st', 'sku_id'];

    /**
     * @return BelongsTo
     */
    public function customProduct()
    {
        return $this->belongsTo(Sku::class, 'sku_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function sku()
    {
        return $this->belongsTo(Sku::class, 'sku_id', 'id');
    }

    /**
     * @param $user_id
     * @return MorphToMany
     */
    public function userMedia($user_id): MorphToMany
    {
        return $this->morphToMany(FileManager::class, 'model',
            'media')->withPivot(['user_id', 'uuid', 'collection', 'size', 'manipulations', 'custom_properties'])
            ->where('media.user_id', $user_id)->withTimestamps();
    }

    /**
     * @return BelongsTo
     */
    public function queues()
    {
        return $this->belongsTo(Queue::class, 'sku_id','queueable_id');
    }

    /**
     *
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('CartVariation')
            ->acceptsMimeTypes(['image/jpeg'])
            ->useDisk('local');
    }
}

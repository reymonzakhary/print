<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'product',
        'boid'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    /**
     * The prices that belong to the product.
     * @return hasMany
     */
    public function prices()
    {
        return $this->hasMany(ProductPrice::class, 'product_id', 'id');
    }

    /**
     * Boop Prices.
     * @return hasOne
     */
    public function boopPrices()
    {
        return $this->hasOne(BoopPrice::class, 'product_id', 'id');
    }

    /**
     * @return array
     */
    function getProductAttribute($value)
    {
        return json_decode($value);
    }
}

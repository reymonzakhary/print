<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model 
{
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['category_id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sku',
        'name',
        'boid'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'category_id'
    ];

    /**
     * @return BelongsToMany
     */
    public function boxes()
    {
        return $this->belongsToMany(Box::class, 'category_boxes');
    }

    /**
     * @return HasOne
     */
    public function boop()
    {
        return $this->hasOne(Boop::class, 'category_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    /**
     * Get box id (id = category_id).
     * used in App\Http\Controllers\Boops\BoopController
     * @return string
     */
    function getCategoryIdAttribute() 
    {
        return $this->id;
    }
}

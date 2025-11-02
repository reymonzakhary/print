<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boop extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'boops',
        'category_id',
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
     * @return array
     */
    function getBoopsAttribute($value) 
    {
        return json_decode($value);
    }
}

<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class DesignProvider extends Model
{


    /**
     * add the relation name for securing the key in db
     * @var string|null
     */
    protected string $relation;

    protected $fillable = [
        'name', 'description', 'settings', 'active', 'type'
    ];

    public function getSettingsAttribute($value)
    {
        return json_decode($value);
    }
}

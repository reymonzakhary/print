<?php

namespace App\Models\Tenants;

use App\Models\Tenant\Orm\BaseModelStatus;

class Status extends BaseModelStatus
{


    protected $fillable = [
        'code', 'name', 'description'
    ];

    /**
     * change the response from id to code
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'name';
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('name', $value)->firstOrFail();
    }
}

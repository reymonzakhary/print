<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Laratrust\Traits\LaratrustUserTrait;

class UserGroup extends Model
{
    use UsesTenantConnection, LaratrustUserTrait;

    /**
     * @var array
     */
    protected $fillable = ['name', 'description'];


    public function contexts()
    {
        return $this->morphedByMany(
            Context::class,
            'accessible',
            'accessible',
            'group_id',
            'accessible_id'
        );
    }

    public function canAccessMgr()
    {
        return $this->contexts()->containes(['mgr']);
    }
}

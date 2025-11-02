<?php


namespace App\Models\Traits\Tenant;


use App\Models\Tenants\Status;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasTenantStatus
{
    /**
     * @return HasOne
     */
    public function status()
    {
        return $this->hasOne(Status::class, 'code', 'st');
    }
}

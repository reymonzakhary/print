<?php


namespace App\Models\Traits\Tenant;


use App\Models\Tenants\Context;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongToTenantContext
{
    /**
     * @return BelongsTo
     */
    public function context()
    {
        return $this->belongsTo(Context::class, 'ctx_id', 'id');
    }
}

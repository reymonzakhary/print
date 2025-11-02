<?php

namespace App\Http\Controllers\Tenant\Mgr\Units;

use App\Http\Controllers\Controller;
use App\Http\Resources\Unit\UnitResource;
use App\Models\Tenants\Unit;

/**
 * @group Tenant Units
 */
class UnitController extends Controller
{
    /**
     * Units
     * 
     * get all units
     * 
     * @apiResource App\Http\Resources\Unit\UnitResource
     * @apiModel App\Models\Tenants\Unit
     * 
     */
    public function __invoke()
    {
        return UnitResource::collection(Unit::all());
    }
}

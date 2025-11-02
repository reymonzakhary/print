<?php

namespace App\Http\Controllers\Tenant\Mgr\Apps;

use App\Http\Controllers\Controller;
use App\Http\Resources\Apps\AppResource;
use App\Models\Tenants\Npace;
use Illuminate\Http\Request;
/**
 * @group Tenant Apps
 * 
 * APIs for managing Apps
 */
class AppController extends Controller
{
    /**
     * Apps 
     * 
     * get tenant apps 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * 
     * @response 200 
     * {
     *      "data":[
     *          {
     *              "namespace": "core",
     *              "enabled": true 
     *          },
     *       ]
     * }
     */
    public function __invoke()
    {
        $hostnameNs = collect(tenant()->hostnames->first()->configure['namespaces'])
            ->pluck('namespace')
            ->flatten()
            ->unique()
            ->toArray();
        
        $tenantNs = Npace::get()->pluck('slug')->flatten();
        
        $availableNs = $tenantNs->map(function ($ns) use ($hostnameNs) {
            if (in_array($ns, $hostnameNs)) {
                return [
                    'namespace' => $ns,
                    'enabled' => true
                ];
            }

            return [
                'namespace' => $ns,
                'enabled' => false
            ];
        });

        return AppResource::collection($availableNs);
    }
}

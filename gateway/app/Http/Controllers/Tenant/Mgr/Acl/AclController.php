<?php

namespace App\Http\Controllers\Tenant\Mgr\Acl;

use App\Http\Controllers\Controller;
use App\Http\Resources\Permissions\PermissionIndexResource;
use App\Models\Tenant\Npace;
use App\Models\Tenant\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant ACL
 */
class AclController extends Controller
{
    /**
     * List ACL permissions
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
	 * "data": [
	 * 	{
	 * 		"id": 1,
	 * 		"name": "core",
	 * 		"slug": "core",
	 * 		"sort": 1,
	 * 		"icon": null,
	 * 		"area": []
	 * 	},
     * ]
     * }
     *
     */
    public function __invoke()
    {
        return PermissionIndexResource::collection(Npace::where('disabled', false)->with('permissions')->whereHas('permissions')->get())->additional([
            'namespace' => Permission::groupBy('namespace')->orderBy('namespace', 'ASC')->pluck('namespace'),
            'area' => Permission::groupBy('area')->orderBy('area', 'ASC')->pluck('area'),
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }
}

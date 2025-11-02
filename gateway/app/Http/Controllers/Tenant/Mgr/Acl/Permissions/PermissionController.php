<?php

namespace App\Http\Controllers\Tenant\Mgr\Acl\Permissions;

use App\Http\Controllers\Controller;

class PermissionController extends Controller
{
//    /**
//     * @OA\Get (
//     *     tags={"Permissions"},
//     *     path="/api/v1/mgr/acl/permission",
//     *     summary="Get All Permissions",
//     *     security={{ "Bearer":{} }},
//     *     @OA\Response(
//     *     response="200", description="success",
//     *     @OA\JsonContent(
//     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PermissionIndexResource")),
//     *      )),
//     *     @OA\Response(response=401, description="Unauthorized"),
//     * )
//     *
//     */
//    public function __invoke(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
//    {
//        return PermissionIndexResource::collection( Npace::all())->additional([
//            'namespace' => Permission::groupBy('namespace')->orderBy('namespace','ASC')->pluck('namespace'),
//            'area' => Permission::groupBy('area')->orderBy('area','ASC')->pluck('area'),
//            'status' => Response::HTTP_OK,
//            'message' => null
//        ]);
//    }
}

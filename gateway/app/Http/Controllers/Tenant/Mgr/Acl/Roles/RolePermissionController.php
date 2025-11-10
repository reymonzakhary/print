<?php

namespace App\Http\Controllers\Tenant\Mgr\Acl\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\AttachPermissionToRoleRequest;
use App\Models\Tenant\Role;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionController extends Controller
{
    /**
     * @OA\Post(
     *     tags={"Roles"},
     *     path="/api/v1/mgr/acl/roles/{role}",
     *     summary="Attach Permission to User",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert Permission data",
     *      @OA\JsonContent(ref="#/components/schemas/AttachPermissionToRoleRequest"),
     *   ),
     *     @OA\Parameter (
     *      name="Role",
     *     in="path",
     *     required=true
     * ),
     *
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(property="id", type="integer", example="1"),
     *          @OA\Property(property="name", type="string", example="superadministrator"),
     *          @OA\Property(property="display_name", type="string", example="superadministrator"),
     *          @OA\Property(property="description", type="string", example="Super Administrator"),
     *          @OA\Property(property="created_at", type="timeData", example="2021-12-05T08:24:57.000000Z"),
     *          @OA\Property(property="updated_at", type="timeData", example="2021-12-05T08:24:57.000000Z"),
     *          @OA\Property(property="message", type="string", example="Permissions has been attached to role."),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=400, description="BAD REQUEST", @OA\JsonContent(
     *     @OA\Property(property="message", type="string", example="We couldn't be able to attach those permissions.")
     *     ))
     * )
     */
    public function __invoke(
        AttachPermissionToRoleRequest $request,
        Role                          $role
    ): JsonResponse
    {
        if ($role->syncPermissions($request->validated('permissions'))) {
            return response()->json([
                'data' => $role,
                'message' => __('Permissions has been attached to role.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        return response()->json([
            'data' => null,
            'message' => __('We couldn\'t be able to attach those permissions.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }
}

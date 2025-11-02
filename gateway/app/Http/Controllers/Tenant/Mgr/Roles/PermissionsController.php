<?php

namespace App\Http\Controllers\Tenant\Mgr\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\PermissionStoreRequest;
use App\Http\Requests\Roles\PermissionUpdateRequest;
use App\Http\Resources\Roles\PermissionResource;
use App\Models\Tenants\Context;
use App\Models\Tenants\Permission;
use App\Models\Tenants\Role;
use App\Models\Tenants\Team;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PermissionsController extends Controller
{
    /**
     * @var int|mixed
     */
    protected int $per_page = 10;

    /**
     * PermissionsController constructor.
     */
    public function __construct()
    {
        /**
         * default number of pages
         */
        $this->per_page = request()->get('per_page') ?? $this->per_page;
    }

    /**
     * Get Teams of Contexts
     *
     * @param Context $context
     * @param Team    $team
     * @param Role    $role
     *
     * @return AnonymousResourceCollection
     */
    public function index(Role $role)
    {
        return PermissionResource::collection($role->permissions()->paginate($this->per_page))
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    public function listAll(Permission $permissions)
    {
        return PermissionResource::collection($permissions->paginate($this->per_page))
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Save New Permission
     *
     * @param Context                $context
     * @param Team                   $team
     * @param Role                   $role
     * @param PermissionStoreRequest $request
     *
     * @return PermissionResource|JsonResponse
     */
    public function store(
        Role                   $role,
        PermissionStoreRequest $request
    )
    {
        $permission = Permission::create($request->validated());
        if ($permission) {
            $role->permissions()->attach($permission);
            return PermissionResource::make($permission)->additional([
                'status' => Response::HTTP_CREATED,
                'message' => __('permissions.created')
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('permissions.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update Permission
     *
     * @param Context                 $context
     * @param Team                    $team
     * @param Role                    $role
     * @param Permission              $permission
     * @param PermissionUpdateRequest $request
     *
     * @return PermissionResource|JsonResponse
     */
    public function update(
        Permission              $permission,
        PermissionUpdateRequest $request
    )
    {
        if ($permission->update($request->validated())) {
            return PermissionResource::make($permission)->additional([
                'status' => Response::HTTP_OK,
                'message' => __('permissions.updated')
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('permissions.updated')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Delete Permission
     *
     * @param Context    $context
     * @param Team       $team
     * @param Role       $role
     * @param Permission $permission
     *
     * @return PermissionResource|JsonResponse
     * @throws Exception
     */
    public function destroy(
        Permission $permission
    )
    {
        if ($permission->delete()) {
            /**
             * success response
             */
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_OK,
                    'message' => __('permission.permission_removed')
                ]
            ], Response::HTTP_OK);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('permissions.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);

    }
}

<?php

namespace App\Http\Controllers\Tenant\Mgr\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\RolePermissionUpdateRequest;
use App\Http\Requests\Roles\RoleStoreRequest;
use App\Http\Requests\Roles\RoleUpdateRequest;
use App\Http\Resources\Roles\RoleResource;
use App\Models\Tenant\Role;
use App\Models\Tenant\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @group Tenant Roles
 */
class RoleController extends Controller
{
    /**
     * @var int|mixed
     */
    protected int $per_page = 10;

    /**
     * RoleController constructor.
     */
    public function __construct()
    {
        /**
         * default number of pages
         */
        $this->per_page = request()->get('per_page') ?? $this->per_page;
    }


    /**
     * List Roles
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *  "data": [
     *      {
	 *		"id": 1,
	 *		"name": "administrator",
	 *		"display_name": "administrator",
	 *		"description": "administrator"
	 *	    },
     *    ],
     * "links":{},
     * "meta":{},
     * "status":200,
     * "message": null
     * }
     *
     * @param Role $roles
     * @return AnonymousResourceCollection
     */
    public function index(Role $roles)
    {
        return RoleResource::collection($roles->paginate($this->per_page))
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Create Role
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @bodyParam name string required unique name of role. Example: newRole
     * @bodyParam display_name string required display name of role. Example: newRole
     * @bodyParam description string The description of role. Example: role description
     *
     * @response 201
     * {
     * "data": {
	 * 	"id": 1,
	 * 	"name": "admin",
	 * 	"display_name": "admin",
	 * 	"description": "admin"
	 * },
	 * "status": 201,
	 * "message": "Your record has been created"
     * }
     *
     * @response 422
     * {
     * 	"message": "The name field is required. (and 1 more error)",
     * 	"errors": {
     * 		"name": [
     * 			"The name field is required."
     * 		],
     * 		"display_name": [
     * 			"The display name field is required."
     * 		]
     * 	}
     * }
     *
     * @response 400
     * {
     * 	"data": {
     * 		"status": 400,
     * 		"message": "We could'not handel your request, please try again later"
     * 	}
     * }
     *
     * @param RoleStoreRequest $request
     * @return RoleResource|JsonResponse
     */
    public function store(
        RoleStoreRequest $request
    )
    {
        $role = Role::create($request->validated());
        if ($role) {
            return RoleResource::make($role)->additional([
                'status' => Response::HTTP_CREATED,
                'message' => __('roles.created')
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('roles.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Update Role
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam role_id integer required The ID of the role.
     *
     * @bodyParam name string required unique name of role. Example: newRole
     * @bodyParam display_name string display name of role. Example: newRole
     * @bodyParam description string The description of role. Example: role description
     *
     * @response 200
     * {
     * 	"data": {
     * 		"id": 3,
     * 		"name": "admin",
     * 		"display_name": "admin",
     * 		"description": "admin description"
     * 	},
     * 	"status": 200,
     * 	"message": "Role has been updated successfully."
     * }
     *
     * @response 422
     * {
     *	"message": "The name field is required.",
     *	"errors": {
     *		"name": [
     *			"The name field is required."
     *		]
     *	}
     * }
     *
     * @response 400
     * {
     * 	"data": {
     * 		"status": 400,
     * 		"message": "We could'not handel your request, please try again later"
     * 	}
     * }
     *
     * @param RoleStoreRequest $request
     * @param Role $role
     * @return RoleResource|JsonResponse
     */
    public function update(
        Role              $role,
        RoleUpdateRequest $request
    )
    {
        if ($role->update($request->validated())) {
            return RoleResource::make($role)->additional([
                'status' => Response::HTTP_OK,
                'message' => __('roles.updated')
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('roles.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Add Permissions to a role
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam role_id integer required The ID of the role.
     *
     * @bodyParam permissions array required names of Permissions.Example: ['auth-access' ,'settings-list']
     *
     * @response 200
     * {
     * 	"data": {
     * 		"id": 3,
     * 		"name": "admin",
     * 		"display_name": "admin",
     * 		"description": "admin description"
     * 	},
     * 	"status": 200,
     * 	"message": "Role has been updated successfully."
     * }
     *
     * @response 422
     * {
     * 	"message": "The selected permissions.0 is invalid.",
     * 	"errors": {
     * 		"permissions.0": [
     * 			"The selected permissions.0 is invalid."
     * 		]
     * 	}
     * }
     *
     * @response 400
     * {
     * 	"data": {
     * 		"status": 400,
     * 		"message": "We could'not handel your request, please try again later"
     * 	}
     * }
     *
     * @param Role $role
     * @param RolePermissionUpdateRequest $request
     * @return RoleResource|JsonResponse
     */
    public function updatePermissions(
        Role                        $role,
        RolePermissionUpdateRequest $request
    )
    {
        if ($role->syncPermissions($request->permissions)) {
            return RoleResource::make($role)->additional([
                'status' => Response::HTTP_OK,
                'message' => __('roles.updated')
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('roles.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Delete Role
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam role_id integer required The ID of the role.
     *
     * @response 200
     * {
     * 	"data": {
     * 		"status": 200,
     * 		"message": "role has been removed."
     * 	}
     * }
     *
     * @response 400
     * {
     * 	"data": {
     * 		"status": 400,
     * 		"message": "We could'not handel your request, please try again later"
     * 	}
     * }
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function destroy(
        Role $role
    )
    {
        if ($role->delete()) {
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_OK,
                    'message' => __('roles.role_removed')
                ]
            ], Response::HTTP_OK);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('roles.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }
}

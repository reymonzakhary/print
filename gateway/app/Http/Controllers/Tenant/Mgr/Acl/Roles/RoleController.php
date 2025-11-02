<?php

namespace App\Http\Controllers\Tenant\Mgr\Acl\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\RoleStoreRequest;
use App\Http\Requests\Roles\RoleUpdateRequest;
use App\Http\Resources\Roles\RoleResource;
use App\Models\Tenants\Role;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant ACL
 * @subgroup ACL Roles
 */
class RoleController extends Controller
{
    /**
     * List Roles
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * "data": [
	 * 	{
	 * 		"id": 2,
	 * 		"name": "quotation_supplier",
	 * 		"display_name": "Quotation Supplier",
	 * 		"description": "Quotation Supplier",
	 * 		"permissions": []
     *     },
     * "message": null,
	 * "status": 200
     *  ]
     * }
     *
     * @return mixed
     */
    public function index(): mixed
    {
        return RoleResource::collection(
            Role::where('name', '!=', 'superadministrator')->with('permissions')->get()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Show Role
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"data": {
     * 		"id": 4,
     * 		"name": "manager",
     * 		"display_name": "Manager",
     * 		"description": "manager"
     * 	},
     * 	"message": null,
     * 	"status": 200
     * }
     *
     * @param Role $role
     * @return RoleResource
     */
    public function show(
        Role $role
    ): RoleResource
    {
        return RoleResource::make($role)
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * @param RoleStoreRequest $request
     * @return RoleResource
     */

    /**
     * Store Acl Role
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 201
     * {
     * 	"data": {
     * 		"id": 4,
     * 		"name": "manager",
     * 		"display_name": "Manager",
     * 		"description": "manager"
     * 	},
     * 	"message": "Role has been created successfully.",
     * 	"status": 201
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
     */

    public function store(
        RoleStoreRequest $request
    ): RoleResource
    {
        return RoleResource::make(
            Role::create($request->validated())
        )->additional([
            'message' => __("Role has been created successfully."),
            'status' => Response::HTTP_CREATED
        ]);
    }

    /**
     * @param RoleUpdateRequest $request
     * @param Role              $role
     * @return array|JsonResponse
     */
    /**
     * Update Role
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"data": {
     * 		"id": 4,
     * 		"name": "manager",
     * 		"display_name": "Manager",
     * 		"description": "manager"
     * 	},
     * 	"message": "Role has been updated successfully.",
     * 	"status": 200
     * }
     *
     * @response 422
     * {
     * 	"message": "The name field is required.",
     * 	"errors": {
     * 		"name": [
     * 			"The name field is required."
     * 		]
     * 	}
     * }
     *
     * @response 403
     * {
     * 	"data": null,
     * 	"message": "Something went wrong.",
     * 	"status": 400
     * }
     *
     */
    public function update(
        RoleUpdateRequest $request,
        Role              $role
    ): JsonResponse
    {
        if ($role->update($request->validated())) {
            return response()->json([
                'data' => null,
                'message' => __("Role has been updated successfully."),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'data' => null,
            'message' => __("Something went wrong."),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Role $role
     * @return bool|null
     * @throws Exception
     */
    /**
     * Delete Role
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"data": null,
     * 	"message": "Role has been deleted successfully",
     * 	"status": 200
     * }
     *
     * @response 400
     * {
     * 	"data": null,
     * 	"message": "We couldn't delete the requested role, please try again later!",
     * 	"status": 400
     * }
     *
     */
    public function destroy(
        Role $role
    )
    {
        if ($role->delete()) {
            return response()->json([
                'data' => null,
                'message' => __("Role has been deleted successfully"),
                'status' => Response::HTTP_OK,
            ], Response::HTTP_OK);
        }
        return response()->json([
            'data' => null,
            'message' => __("We couldn't delete the requested role, please try again later!"),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }
}

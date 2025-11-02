<?php

namespace App\Http\Controllers\Tenant\Mgr\Acl\Roles;

use App\Http\Controllers\Controller;
use App\Http\Resources\Roles\PermissionResource;
use App\Models\Tenants\Role;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PermissionController extends Controller
{
    /**
     * @param Role $role
     * @return AnonymousResourceCollection
     */
    public function __invoke(
        Role $role
    ): AnonymousResourceCollection
    {
        return PermissionResource::collection(
            $role->permissions
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }
}

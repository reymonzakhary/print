<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AttachPermissionToRoleRequest
 * @package App\Http\Resources\Roles
 * @OA\Schema(
 *     schema="AttachPermissionToRoleRequest",
 *     title="assing Permissions To Role"
 *
 * )
 */
class AttachPermissionToRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    /**
     * @OA\Property(format="array", title="permissions", default="", description="permissions", example="{'account-settings-list', 'settings-delete'}", property="permissions"),
     */
    public function rules()
    {
        return [
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id|integer'
        ];
    }
}

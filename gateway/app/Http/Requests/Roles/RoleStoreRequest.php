<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Class RoleStoreRequest
 * @package App\Http\Resources\Roles
 * @OA\Schema(
 *     schema="RoleStoreRequest",
 *     title="Role Store Request"
 *
 * )
 */
class RoleStoreRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", default="", description="name", example="superadministrator", property="name"),
     * @OA\Property(format="string", title="display_name", default="", description="display_name", property="display_name"),
     * @OA\Property(format="string", title="description", default="Use Role", description="description", property="description"),
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255|unique:roles',
            'display_name' => 'required|max:255',
            'description' => 'max:255'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => Str::slug($this->display_name)
        ]);
    }
}

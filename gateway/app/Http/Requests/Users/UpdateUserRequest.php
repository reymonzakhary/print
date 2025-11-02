<?php

namespace App\Http\Requests\Users;

use App\Enums\MemberType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Class UpdateUserRequest
 * @package App\Http\Requests\Members
 * @OA\Schema(
 * )
 */
class UpdateUserRequest extends FormRequest
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
     * @OA\Property(format="string", title="ctx_id",example="[1,2]", description="required|integer|exists:contexts,id", property="ctx_id"),
     * @OA\Property(format="string", title="gender", default="null",example="male", description="in:male,female,other|nullable", property="gender"),
     * @OA\Property(format="string", title="first_name", example="ahmed", description="required|string|min:2", property="first_name"),
     * @OA\Property(format="string", title="last_name", default="null",example="hifny", description="required|string|min:2", property="last_name"),
     * @OA\Property(format="string", title="dob", default="null",example="26-9-1993", description="date|nullable", property="dob"),
     * @OA\Property(format="string", title="bio", default="null",example="long text about me", description="string|nullable", property="bio"),
     * @OA\Property(format="string", title="avatar", default="null",example="image url", description="string|nullable", property="avatar"),
     * @OA\Property(format="string", title="custom_field", default="null",example="[{'dummy':'data'}]", description="array|nullable", property="custom_field"),
     * @OA\Property(format="string", title="email",example="example@email.com", description="required|unique:users|string|email|max:255", property="email"),
     * @OA\Property(format="string", title="roles",example="[1,2]", description="exists:roles,name|nullable", property="roles"),
     * @OA\Property(format="string", title="teams",example="[1,2]", description="exists:teams,id|nullable", property="teams"),
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'gender' => 'in:male,female,other',
            'first_name' => 'string|min:2',
            'middle_name' => 'string|min:2',
            'last_name' => 'string|min:2',
            'dob' => 'date|nullable',
            'avatar' => 'string|nullable',
            'bio' => 'string|nullable',
            'custom_field' => 'array|nullable',

            'ctx_id.*' => 'integer|exists:contexts,id',
            'email' => 'string|email|max:255|unique:users,email,' . $this->user,

            'roles' => 'array|nullable',
            'roles.*' => 'exists:roles,id|nullable',

            'member'        => 'required|boolean',
            'teams' => 'array|nullable',
            'teams.*.id' => 'exists:teams,id|nullable',
            'teams.*.admin' => 'boolean',
            'teams.*.authorizer' => 'boolean',
            'type' => ['required', 'string', new Enum(MemberType::class)],

        ];
    }


    /**
     *
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'type' => 'individual',
            'member' => false
        ]);
    }

}

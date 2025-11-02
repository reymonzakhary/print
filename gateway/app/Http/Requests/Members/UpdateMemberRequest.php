<?php

namespace App\Http\Requests\Members;

use App\Enums\MemberType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Class UpdateMemberRequest
 * @package App\Http\Requests\Members
 * @OA\Schema(
 * )
 */
class UpdateMemberRequest extends FormRequest
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
     * @OA\Property(format="string", title="ctx_id",example="[1,2,4]", description="integer|exists:contexts,id", property="ctx_id"),
     * @OA\Property(format="string", title="gender", example="male", description="in:male,female,other", property="gender"),
     * @OA\Property(format="string", title="first_name", example="ahmed", description="string|min:2", property="first_name"),
     * @OA\Property(format="string", title="middle_name", default="null",example="mohamed", description="string|nullable", property="middle_name"),
     * @OA\Property(format="string", title="last_name", default="null",example="hifny", description="string|min:2", property="last_name"),
     * @OA\Property(format="string", title="dob", default="null",example="26-9-1993", description="date|nullable", property="dob"),
     * @OA\Property(format="string", title="bio", default="null",example="long text about me", description="string|nullable", property="bio"),
     * @OA\Property(format="string", title="avatar", default="null",example="image url", description="string|nullable", property="avatar"),
     * @OA\Property(format="string", title="custom_field", default="null",example="[{'dummy':'data'}]", description="array|nullable", property="custom_field"),
     * @OA\Property(format="string", title="username", default="false",example="false", description="required|unique:users,username|string", property="username"),
     * @OA\Property(format="string", title="email",example="example@email.com", description="required|unique:users|string|email|max:255", property="email"),
     * @OA\Property(format="string", title="roles",example="[1,2,3]", description="exists:roles,id|nullable", property="roles"),
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ctx_id.*' => 'integer|exists:contexts,id',
            'gender' => 'in:male,female,other',
            'first_name' => 'string|min:2',
            'middle_name' => 'string|nullable',
            'last_name' => 'string|min:2',
            'dob' => 'date|nullable',
            'bio' => 'string|nullable',
            'avatar' => 'string|nullable',
            'custom_field' => 'array|nullable',
//            'username' => 'string|unique:users,username,' . $this->user,
            'email' => 'string|email|max:255|unique:users,email,' . $this->route('member'),
            'roles.*' => 'exists:roles,id|nullable',
            'member' => 'required|boolean',
            'type' => ['required', 'string', new Enum(MemberType::class)],

            'teams' => 'array|nullable',
            'teams.*.id' => 'exists:teams,id|nullable',
            'teams.*.admin' => 'boolean',
            'teams.*.authorizer' => 'boolean',

            'company' => 'integer|required_if:type,'.MemberType::BUSINESS->value,
        ];

    }

    /**
     *
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'member' => true
        ]);
    }
}

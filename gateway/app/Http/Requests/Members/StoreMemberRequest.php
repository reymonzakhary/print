<?php

namespace App\Http\Requests\Members;

use App\Enums\MemberType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Class StoreMemberRequest
 * @package App\Http\Requests\Members
 * @OA\Schema(
 * )
 */
class StoreMemberRequest extends FormRequest
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
     * @OA\Property(format="string", title="gender", default="null",example="male", description="in:male,female,other|nullable", property="gender"),
     * @OA\Property(format="string", title="first_name", example="ahmed", description="required|string|min:2", property="first_name"),
     * @OA\Property(format="string", title="middle_name", default="null",example="mohamed", description="string|nullable", property="middle_name"),
     * @OA\Property(format="string", title="last_name", default="null",example="hifny", description="required|string|min:2", property="last_name"),
     * @OA\Property(format="string", title="dob", default="null",example="26-9-1993", description="date|nullable", property="dob"),
     * @OA\Property(format="string", title="bio", default="null",example="long text about me", description="string|nullable", property="bio"),
     * @OA\Property(format="string", title="avatar", default="null",example="image url", description="string|nullable", property="avatar"),
     * @OA\Property(format="string", title="custom_field", default="null",example="[{'dummy':'data'}]", description="array|nullable", property="custom_field"),
     * @OA\Property(format="string", title="username", default="false",example="false", description="required|unique:users,username|string", property="username"),
     * @OA\Property(format="string", title="email",example="example@email.com", description="required|unique:users|string|email|max:255", property="email"),
     * @OA\Property(format="string", title="role",example="admin", description="exists:roles,name|nullable", property="role"),
     * @OA\Property(format="string", title="password", default="auto generated password",example="tada@3030Hehe#", description="required|string|min:10|regex:/[a-z]/|regex:/[A-Z]/|regex:/[@$!%*#?&]/", property="password"),
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'gender'        => 'in:male,female,other|nullable',
            'first_name'    => 'required|string|min:2',
            'middle_name'   => 'string|nullable',
            'last_name'     => 'required|string|min:2',
            'dob'           => 'date|nullable',
            'bio'           => 'string|nullable',
            'avatar'        => 'string|nullable',
            'custom_field'  => 'array|nullable',
            'email'         => 'required|unique:users|string|email|max:255',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name|nullable',
            'ctx_id'        => 'required|integer|exists:contexts,id',
            'member'        => 'required|boolean',
            'type'          => ['required','string', new Enum(MemberType::class)],

            'teams' => 'array|nullable',
            'teams.*.id' => 'exists:teams,id',
            'teams.*.admin' => 'nullable|boolean',
            'teams.*.authorizer' => 'nullable|boolean',

            'company' => 'integer|required_if:type,'.MemberType::BUSINESS->value,

            'generated_password' => 'nullable',
            'password' => [
                'required',
                'string',
                'min:10',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ],
        ];
    }


    protected function prepareForValidation()
    {
        $prepare = [
            'generated_password' => $this->password ? false : true,
            'password' => random_password(15).rand(),
            'member' => true,
            'ctx_id' => $this->ctx_id??1,
        ];
        $this->merge($prepare);
    }




    public function messages()
    {
        return [
            'password.required' => __('The password field is required.'),
        ];
    }
}

<?php

namespace App\Http\Requests\Users;

use App\Enums\MemberType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Class StoreUserRequest
 * @package App\Http\Requests\Members
 * @OA\Schema(
 * )
 */
class StoreUserRequest extends FormRequest
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
     * @OA\Property(format="string", title="ctx_id",example="1", description="required|integer|exists:contexts,id", property="ctx_id"),
     * @OA\Property(format="string", title="gender", default="null",example="male", description="in:male,female,other|nullable", property="gender"),
     * @OA\Property(format="string", title="first_name", example="ahmed", description="required|string|min:2", property="first_name"),
     * @OA\Property(format="string", title="last_name", default="null",example="hifny", description="required|string|min:2", property="last_name"),
     * @OA\Property(format="string", title="dob", default="null",example="26-9-1993", description="date|nullable", property="dob"),
     * @OA\Property(format="string", title="bio", default="null",example="long text about me", description="string|nullable", property="bio"),
     * @OA\Property(format="string", title="avatar", default="null",example="image url", description="string|nullable", property="avatar"),
     * @OA\Property(format="string", title="custom_field", default="null",example="[{'dummy':'data'}]", description="array|nullable", property="custom_field"),
     * @OA\Property(format="string", title="email",example="example@email.com", description="required|unique:users|string|email|max:255", property="email"),
     * @OA\Property(format="string", title="roles",example="[admin,accountant]", description="exists:roles,name|nullable", property="roles"),
     * @OA\Property(format="string", title="teams",example="[1,2]", description="exists:teams,id|nullable", property="teams"),
     * @OA\Property(format="string", title="password", default="auto generated password",example="tada@3030Hehe#", description="required|string|min:10|regex:/[a-z]/|regex:/[A-Z]/|regex:/[@$!%*#?&]/", property="password"),
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'ctx_id' => 'required|integer|exists:contexts,id',
            'gender' => 'in:male,female,other|nullable',
            'first_name' => 'required|string|min:2',
            'last_name' => 'required|string|min:2',
            'dob' => 'date|nullable',
            'bio' => 'string|nullable',
            'avatar' => 'string|nullable',
            'custom_field' => 'array|nullable',
//            'username'      => 'string|required|unique:users',
            'email' => 'required|unique:users|string|email|max:255',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name|nullable',
            'member'        => 'required|boolean',
            'teams' => 'array',
            'teams.*.id' => 'exists:teams,id|nullable',
            'teams.*.admin' => 'boolean',
            'teams.*.authorizer' => 'boolean',
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
            'type' => ['required', 'string', new Enum(MemberType::class)],
        ];
    }

    /**
     *
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'generated_password' => !$this->password,
            'password' => $this->password ?? random_password(15) . random_int(8, 20),
            'ctx_id' => 2,
            'type' => 'individual',
            'member' => false
        ]);
    }


    public function messages()
    {
        return [
            'password.required' => __('The password field is required.'),
        ];
    }
}

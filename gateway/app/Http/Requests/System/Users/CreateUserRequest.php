<?php

namespace App\Http\Requests\System\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
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
    public function rules()
    {
        return [
            'gender' => 'in:male,female,other|nullable',
            'first_name' => 'required|string|min:2',
            'last_name' => 'required|string|min:2',
            'dob' => 'date|nullable',
            'bio' => 'string|nullable',
            'avatar' => 'string|nullable',
            'custom_field' => 'array|nullable',
            'email' => 'required|unique:users|string|email|max:200',
            'password' => 'required',
            'username' => 'required|unique:users|string', //@todo has to be removed
            'company_name' => 'required|unique:companies,name',
            'description' => 'required|min:3',
            'tax_nr' => 'string',
            'url' => 'required|url',
            'coc' => 'string',
            'authorization' => 'nullable|in:Bearer,password',
            'authToken' => [Rule::requiredIf($this->authorization !== 'password')],
            'authUsername' => [Rule::requiredIf($this->authorization === 'password')],
            'authPassword' => [Rule::requiredIf($this->authorization === 'password')],

            'address' => 'required|string',
            'number' => 'required',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'region' => 'string|nullable',
            'country_id' => 'required|integer',
            'type' => 'string|nullable|in:invoice,home,work,primary,delivery,other',
            'default' => 'boolean',
            'roles' => 'array|nullable',
        ];
    }


    protected function prepareForValidation()
    {
        $this->merge([
            'username' => $this->email,
            'roles' => empty($this->roles)?[1]:$this->roles
        ]);
    }
}

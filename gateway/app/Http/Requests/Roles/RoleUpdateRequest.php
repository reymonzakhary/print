<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleUpdateRequest extends FormRequest
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
            'name' => [
                'required',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->role),
            ],
            'display_name' => 'string|max:255',
            'description' => [
                'max:255',
            ]
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => Str::slug($this->display_name)
        ]);
    }
}

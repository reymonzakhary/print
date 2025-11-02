<?php


namespace App\Http\Requests\Roles;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionUpdateRequest extends FormRequest
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
                Rule::unique('permissions', 'name')->ignore($this->permission),
            ],
            'display_name' => 'required|max:255',
            'description' => 'max:255',
        ];
    }
}

<?php

namespace App\Http\Requests\Members;

use Illuminate\Foundation\Http\FormRequest;

class DetachTeamMembersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    /**
     * Get custom validation error messages for the request.
     *
     * This method overrides the default Laravel validation messages to provide
     * more user-friendly and context-specific error responses for the ids array.
     *
     * @return array<string, string> An array of field-specific validation messages.
     */
    public function messages(): array
    {
        return [
            'ids.required'     => __('Please specify one or more user IDs to detach.'),
            'ids.array'        => __('The provided IDs must be in an array format.'),
            'ids.min'          => __('You must select at least one user to detach.'),
            'ids.*.integer'    => __('Each user ID must be a valid integer.'),
            'ids.*.distinct'   => __('Duplicate user IDs are not allowed.'),
            'ids.*.exists'     => __('One or more of the selected users do not exist.'),
        ];
    }
}

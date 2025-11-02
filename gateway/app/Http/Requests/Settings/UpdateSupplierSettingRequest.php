<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateSupplierSettingRequest extends FormRequest
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
            'sort' => 'nullable|integer',
            'data_type' => 'string|nullable',
            'data_variable' => 'string|nullable',
            'secure_variable' => 'boolean|nullable',
            'multi_select' => 'boolean|nullable',
            'incremental' => 'boolean|nullable',
            'lexicon' => 'nullable|string',
            'value' => 'nullable'
        ];
    }

    protected function prepareForValidation()
    {
        if (!collect(optional(request()->tenant->configure)['settings'])->filter(function ($setting) {
            return $setting['key'] === $this->key;
        })->count()) {
            throw ValidationException::withMessages([
                'key' => __('No query results for key ' . $this->key)
            ]);
        }
    }
}

<?php

namespace App\Http\Requests\Teams;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreAccessibilityRequest extends FormRequest
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
            'model' => 'string|in:category,product,users,externalCategory,externalUser,externalProduct',
            'model_id' => 'required'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if (!is_numeric($this->model_id)) {
            $this->merge([
                'model' => 'external'.Str::ucfirst($this->model)
            ]);
        }
    }
}

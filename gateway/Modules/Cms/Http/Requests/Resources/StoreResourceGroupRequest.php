<?php

namespace Modules\Cms\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;

class StoreResourceGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:resource_groups,name',
            'sort' => 'integer|nullable'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}

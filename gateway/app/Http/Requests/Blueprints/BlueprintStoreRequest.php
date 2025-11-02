<?php

namespace App\Http\Requests\Blueprints;

use Illuminate\Foundation\Http\FormRequest;

class BlueprintStoreRequest extends FormRequest
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
            'name' => 'required',
            'ns' => 'required|string|in:orders.system,orders.mgr,orders.api,webworkflow_shop,checkout,cart,shop',
            'blueprint' => 'array',
            'configuration' => 'array',
            'sort' => 'integer'
        ];
    }

}

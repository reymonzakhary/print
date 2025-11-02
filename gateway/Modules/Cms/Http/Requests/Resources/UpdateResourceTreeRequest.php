<?php

namespace Modules\Cms\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResourceTreeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sort' => 'array|nullable',
            'sort.*.id' => 'required|integer|exists:resources,resource_id',
            'sort.*.isfolder' => 'boolean|nullable',
            'sort.*.published' => 'boolean|nullable',
            'sort.*.hidden' => 'boolean|nullable',
            'sort.*.searchable' => 'boolean|nullable',
            'sort.*.cacheable' => 'boolean|nullable',
            'sort.*.hide_children_in_tree' => 'boolean|nullable',
            'sort.*.parent_id' => 'nullable|exists:resources,resource_id'
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

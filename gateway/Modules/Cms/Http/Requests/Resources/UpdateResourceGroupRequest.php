<?php

namespace Modules\Cms\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResourceGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                Rule::unique('resource_groups')->ignore($this->group),
            ],
            'resource_id' => 'required_if:attach,resource|exists:resources,resource_id,deleted_at,NULL|integer',
            'team_id' => 'required_if:attach,team|exists:teams,id|integer',
            'attach' => 'in:resource,team|nullable',
            'detach' => 'in:resource,team|nullable'
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

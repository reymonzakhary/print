<?php

namespace Modules\Cms\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Cms\Entities\ResourceType;

class StoreResourceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|min:1|max:255',
            'slug' => 'string|min:1|max:255',
            'long_title' => 'nullable|string|max:255',
            'intro_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'menu_title' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:4',
            'content' => 'array|nullable',
            'sort' => 'integer|nullable',
            'isfolder' => 'boolean|nullable',
            'published' => 'boolean|nullable',
            'hidden' => 'boolean|nullable',
            'searchable' => 'boolean|nullable',
            'cacheable' => 'boolean|nullable',
            'hide_children_in_tree' => 'boolean|nullable',
            'created_by' => 'exists:users,id',
            'published_by' => 'exists:users,id',
            'template_id' => 'exists:templates,id',
            'ctx_id' => 'nullable|exists:contexts,id',
            'parent_id' => 'nullable|exists:resources,resource_id',
            'resource_type_id' => 'exists:resource_types,id',
            'category' => 'nullable'
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

    protected function prepareForValidation()
    {
        $resourceType = null;
        if ($this->resource_type_id){
            $resourceType = ResourceType::find($this->resource_type_id)?->name;
        }

        $this->merge([
            'created_by' => Auth::user()->id,
            'resource_type' => $resourceType
        ]);
    }
}

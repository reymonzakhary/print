<?php

namespace Modules\Cms\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Entities\ResourceType;
use Modules\Cms\Enums\BlockKeysEnum;

class UpdateResourceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'string|min:1|max:255',
            'slug' => 'string|min:1|max:255',
            'long_title' => 'nullable|string|max:255',
            'intro_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'menu_title' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:4',
            'content' => 'array|nullable',
            'sort' => 'integer|nullable',
            'isfolder' => 'boolean|nullable',
            'menu_index' => 'integer|nullable',
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
            'resource_type' => 'required',
            'image' => 'nullable',
            'files' => 'array',
            'category' => 'nullable',
            'resource' => 'required',
            'uri' => 'nullable'
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
        $resource = Resource::where([['language', app()->getLocale()], ['resource_id', $this->resource]])->firstOrFail();

        $resourceType = null;
        if ($this->resource_type_id){
            $resourceType = ResourceType::find($this->resource_type_id)?->name;
        }

        $this->merge([
            'category' => optional(
                collect($this->get('content'))
                    ->filter(fn ($item) => $item['key'] == BlockKeysEnum::CATEGORY->value || $item['key'] == BlockKeysEnum::BOOPS->value)
                    ->first())['value'],
            'resource_type' => $resourceType,
            'resource' => $resource
        ]);

        if ($this->slug) {
            $uri = explode('/', $resource->uri);
            $index = count($uri) - 1;
            $uri[$index] = $this->slug?? $resource->slug;
            $uri = implode('/', $uri);
            $this->merge([
                'slug' => Str::slug($this->slug),
                'uri' => $uri
            ]);
        }

    }
}

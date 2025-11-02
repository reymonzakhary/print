<?php

namespace App\Http\Resources\DesignProviderTemplates;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignProviderTemplateResource extends JsonResource
{
    protected array $defaultHide = ['created_at'];
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @param mixed $resource
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection($resource)
    {
        return tap(new DesignProviderTemplateResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'settings' => $this->settings,
            'design_provider' => $this->whenLoaded('designProvider'),
            'assets' => $this->assets,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'icon' => $this->icon,
            'type' => $this->type,
            'locked' => $this->locked,
            'folder' => $this->folder,
            'properties' => $this->properties,
            'content' => $this->content,
            'static' => $this->static,
            'path' => $this->path,
            'sort' => $this->sort,
            'created_by' => $this->created_by
        ]);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    public function hide(array $fields)
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param $array
     * @return array
     */
    protected function filterFields(array $array)
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}

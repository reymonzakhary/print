<?php

namespace App\Http\Resources\Items;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderItemServiceResourceCollection extends ResourceCollection
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->processCollection($request);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    final public function hide(array $fields): self
    {
        $this->withoutFields = $fields;
        return $this;
    }

    /**
     * Send fields to hide to TopicsResource while processing the collection.
     *
     * @param $request
     * @return array
     */
    final protected function processCollection($request): array
    {
        return $this->collection->map(fn(OrderItemServiceResource $resource) => $resource->hide($this->withoutFields)->toArray($request))->all();
    }
}

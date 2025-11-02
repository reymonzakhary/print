<?php

namespace App\Http\Resources\Orders;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderActivityResourceCollection extends ResourceCollection
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @var array
     */
    protected array $withoutChildrenFields = [];

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
     * Set the keys that are supposed to be filtered out from children tables.
     * @param array $fields
     * @return $this
     */
    final public function hideChildren(array $fields): self
    {
        $this->withoutChildrenFields = $fields;
        return $this;
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
     * @return \Illuminate\Support\Collection
     */
    final protected function processCollection($request)
    {
        return $this->collection->map(fn(OrderActivityResource $resource) => $resource
            ->hide($this->withoutFields)
            ->hideChildren($this->withoutChildrenFields)
            ->toArray($request))->groupBy(fn($date) => Carbon::parse($date['created_at'])->format('F Y'));
    }
}

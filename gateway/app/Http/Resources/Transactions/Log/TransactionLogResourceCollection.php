<?php

declare(strict_types=1);

namespace App\Http\Resources\Transactions\Log;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class TransactionLogResourceCollection extends ResourceCollection
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
    public function toArray(
        Request $request
    ): array
    {
        return $this->processCollection($request);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    public function hide(
        array $fields
    ): self
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Send fields to hide to TopicsResource while processing the collection.
     *
     * @param Request $request
     *
     * @return array
     */
    protected function processCollection(
        Request $request
    ): array
    {
        return $this->collection->map(
            fn(TransactionLogResource $resource) => $resource->hide($this->withoutFields)->toArray($request)
        )->all();
    }
}

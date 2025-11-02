<?php

declare(strict_types=1);

namespace App\Http\Resources\Transactions\Log;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

final class TransactionLogResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->filterFields([
            'id' => $this->resource->getAttribute('id'),

            'transaction' => $this->whenLoaded('transaction'),

            'st' => $this->resource->getAttribute('st'),
            'st_message' => $this->resource->getAttribute('st_message'),

            'type' => $this->resource->getAttribute('type'),
            'payload' => $this->resource->getAttribute('payload'),

            'updated_at' => $this->resource->getAttribute('updated_at'),
            'created_at' => $this->resource->getAttribute('created_at'),
        ]);
    }

    /**
     * @param mixed $resource
     *
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection(
        $resource
    ): mixed
    {
        return tap(new TransactionLogResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     *
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
     * Remove the filtered keys.
     *
     * @param array $array
     *
     * @return array
     */
    protected function filterFields(
        array $array
    ): array
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\Plugins;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ConfigurationRouteResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return $this->filterFields([
            'method' => $this->resource['method'],
            'route' => $this->resource['route'],
        ]);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    public function hide(array $fields): static
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param array $array
     * @return array
     */
    protected function filterFields(array $array): array
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}

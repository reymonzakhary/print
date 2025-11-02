<?php

namespace App\Http\Resources\Categories;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\NoReturn;

class SystemCategoryResourceCollection extends ResourceCollection
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @var mixed
     */
    private mixed $pagination;

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
    #[NoReturn] final protected function processCollection($request): array
    {
        $this->pagination = $this->preparePaginatedObject($this->collection);
        return $this->collection->filter(fn($collection, $key) => $key === 'data')
            ->map(fn($resource) => collect($resource->resource)
                ->map(function ($resource) use ($request) {
                    $resource = new SystemCategoryResource($resource);
                    return $resource->hide($this->withoutFields)
                        ->toArray($request);
                })
            )
            ->put('links', $this->preparePagination($request))
            ->put('meta', $this->prepareMeta($request))->all();
    }

    /**
     * @param Collection $collection
     * @return array
     */
    protected function preparePaginatedObject(collection $collection): array
    {
        return array_map(function ($collect) {
            return $collect->resource;
        }, Arr::except($collection->toArray(), ['data']));
    }

    /**
     * @param $request
     * @return array
     */
    #[NoReturn] protected function preparePagination($request): array
    {
        if (!$this->pagination) {
            return [];
        }
        return [
            'first' => $this->getUrlOrNone($request, 'first_page_url'),
            'last' => $this->getUrlOrNone($request, 'last_page_url'),
            'prev' => $this->getUrlOrNone($request, 'prev_page_url'),
            'next' => $this->getUrlOrNone($request, 'next_page_url'),
        ];
    }


    /**
     * @param $request
     * @return array
     */
    protected function prepareMeta($request): array
    {
        if (!$this->pagination) {
            return [];
        }
        return [
            'current_page' => $this->pagination['current_page'] ?: null,
            'from' => $this->pagination['from'] ?: null,
            'last_page' => $this->pagination['last_page'] ?: null,
            'links' => [
                [
                    'url' => null,
                    'label' => "&laquo; Previous",
                    'active' => (bool)$this->pagination['prev_page_url'],
                ],
                [
                    'url' => null,
                    "label" => $this->pagination['current_page'],
                    "active" => true
                ],
                [
                    'url' => null,
                    "label" => "Next &raquo;",
                    "active" => (bool)$this->pagination['next_page_url']
                ]
            ],
            "path" => $request->url(),
            "per_page" => $this->pagination['per_page'],
            "to" => $this->pagination['to'],
            "total" => $this->pagination['total'],
        ];
    }

    private function getUrlOrNone($request, $pageType): ?string
    {
        return $this->pagination[$pageType]
            ? "{$request->url()}/{$this->pagination[$pageType]}"
            : null;
    }
}

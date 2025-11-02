<?php

namespace App\Http\Resources\Options;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

class PrintOptionResourceCollection extends ResourceCollection
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
    final protected function processCollection($request): array
    {
        $this->pagination = $this->preparePaginatedObject($this->collection);
        return $this->collection->filter(fn($collection, $key) => $key === 'data')->map(fn($resource) => collect($resource->resource)->map(function ($resource) use ($request) {
            $resource = new PrintOptionResource($resource);
            return $resource->hide($this->withoutFields)->toArray($request);
        }))->put('links', $this->praperPagination($request))->put('meta', $this->praperMeta($request))->all();
    }

    /**
     * @param Collection $request
     * @return mixed
     */
    protected function preparePaginatedObject(collection $request): mixed
    {
        return $this->collection->filter(fn($collection, $key) => $key === 'pagination')->values()->first();
    }

    /**
     * @param $request
     * @return array
     */
    protected function praperPagination($request): array
    {
        if (!$this->pagination) {
            return [];
        }
        return [
            'first' => $this->pagination['first_page_url'] ? "{$request->url()}/{$this->pagination['first_page_url']}" : null,
            'last' => $this->pagination['last_page_url'] ? "{$request->url()}/{$this->pagination['last_page_url']}" : null,
            'prev' => $this->pagination['prev_page_url'] ? "{$request->url()}/{$this->pagination['prev_page_url']}" : null,
            'next' => $this->pagination['next_page_url'] ? "{$request->url()}/{$this->pagination['next_page_url']}" : null,
        ];
    }


    /**
     * @param $request
     * @return array
     */
    protected function praperMeta($request): array
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

}

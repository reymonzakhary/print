<?php

namespace App\Services\Tenant\Finder\Boxes;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Service for searching boxes in the finder system.
 */
class BoxSearchService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * Initialize the service with search base URI and tenant ID.
     */
    public function __construct()
    {
        $this->base_uri = config('services.search.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
    }

    /**
     * Search for boxes using the external search service.
     * 
     * @param array $params Search parameters
     * @return mixed Search results
     * @throws GuzzleException
     */
    final public function obtainFinderSearchBoxes(
        array $params
    ): mixed
    {
        return $this->makeRequest(method:'get', requestUrl: "boxes-search", queryParams: $params);
    }
} 
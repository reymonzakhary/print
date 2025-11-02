<?php

namespace App\Services\Tenant\Finder\Options;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Service for searching options in the finder system.
 */
class OptionSearchService extends ServiceContract
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
     * Search for options using the external search service.
     * 
     * @param array $params Search parameters
     * @return mixed Search results
     * @throws GuzzleException
     */
    final public function obtainFinderSearchOptions(
        array $params
    ): mixed
    {
        return $this->makeRequest(method:'get', requestUrl: "options-search", queryParams: $params);
    }
} 
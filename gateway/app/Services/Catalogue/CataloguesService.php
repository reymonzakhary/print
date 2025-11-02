<?php

namespace App\Services\Catalogue;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class CataloguesService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * set Price base service uri
     */
    public function __construct()
    {
        $this->base_uri = config('services.assortments.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
        $this->tenant_name = optional(request()->hostname)->fqdn;
    }

    /*
    |----------------------------------------------------------------
    | Catalog list for system users
    |----------------------------------------------------------------
    */


    /**
     * @return string|array
     * @throws GuzzleException
     */
    public function obtainCatalogues(): string|array
    {
        return $this->makeRequest('GET', 'catalogues');
    }


    /**
     * @param array $catalogues
     * @return string|array
     * @throws GuzzleException
     */
    public function obtainCreatedCatalogues(
        array $catalogues
    ): string|array
    {
        return $this->makeRequest('POST', 'catalogues', formParams: $catalogues);
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainOptions()
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/options");
    }

    final public function obtainOption(
        string $option
    )
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/options/{$option}");
    }
}

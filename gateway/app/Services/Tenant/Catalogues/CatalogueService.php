<?php

namespace App\Services\Tenant\Catalogues;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class CatalogueService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * CategoryService constructor.
     */
    public function __construct()
    {
        $this->base_uri = config('services.catalogues.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
        $this->tenant_name = optional(request()->hostname)->fqdn;
    }

    /**
     * @return array|string
     * @throws GuzzleException
     */
    final public function obtain(): array|string
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/catalogues");
    }

    /**
     * Create a new catalogue.
     *
     * @param array $request
     * @return array|string
     * @throws GuzzleException
     */
    final public function obtainCreate(
        array  $request
    ): array|string
    {
        $request['tenant_name'] = $this->tenant_name;
        return $this->makeRequest(
            method: 'POST',
            requestUrl: "suppliers/{$this->tenant_id}/catalogues",
            formParams: $request,
            forceJson: true
        );
    }

    /**
     * Update a catalogue.
     *
     * @param string $catalogue
     * @param array  $request
     * @return array|string
     * @throws GuzzleException
     */
    final public function obtainUpdate(
        string $catalogue,
        array  $request
    ): array|string
    {
        return $this->makeRequest(
            method: 'PUT',
            requestUrl: "suppliers/{$this->tenant_id}/catalogues/{$catalogue}",
            formParams: $request,
            forceJson: true
        );
    }

    /**
     * Deletes a catalogue.
     *
     * @param string $catalogue The catalogue to be deleted.
     *
     * @return array|string The result of the delete request.
     * @throws GuzzleException
     */
    final public function obtainDelete(
        string $catalogue
    ): array|string
    {
        return $this->makeRequest(
            method: 'DELETE',
            requestUrl: "suppliers/{$this->tenant_id}/catalogues/{$catalogue}"
        );
    }


}

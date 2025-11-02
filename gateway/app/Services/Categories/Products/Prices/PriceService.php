<?php


namespace App\Services\Categories\Products\Prices;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class PriceService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.prices.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
    }

    final public function obtainCalculatedPrices(
        string $category,
        array  $filter
    )
    {
        return $this->makeRequest('post',
            "calculate/{$this->tenant_id}/{$category}", [], $filter);
    }

    /**
     * @param string $category
     * @param array  $filter
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCalculatePrices(
        string $category,
        array  $filter
    ): string|array
    {
        return $this->makeRequest('post',
            "calculate/{$this->tenant_id}/{$category}/price", [], $filter);
    }

    /**
     * obtain collection prices from the price service
     * @param string $category
     * @param array  $filter
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCollectionPrices(
        string $category,
        array  $filter
    ): string|array
    {
        return $this->makeRequest('post',
            "shop/suppliers/{$this->tenant_id}/categories/{$category}/products/calculate/price/collection", [], $filter, forceJson: true);
    }

    /**
     * @param string $category
     * @param string $combination
     * @param array  $filter
     * @return string
     * @throws GuzzleException
     */
    final public function obtainStorePrices(
        string $category,
        string $combination,
        array  $filter
    )
    {
        return $this->makeRequest('post',
            "prices/{$this->tenant_id}/{$category}/{$combination}", [], $filter);
    }

    /**
     * @param array $machine
     * @return string
     * @throws GuzzleException
     */
    final public function obtainStoreMachine(
        array $machine,
    )
    {
        return $this->makeRequest('post',
            "suppliers/{$this->tenant_id}/machines", [], [
                "machine" => $machine,
                "tenant_name" => tenant()?->hostnames->first()?->fqdn
            ], forceJson:true);
    }

    /**
     * @param array $machine
     * @param       $options
     * @return string
     * @throws GuzzleException
     */
    final public function obtainUpdateMachine(
        array $machine,
        $options
    )
    {
        return $this->makeRequest('put',
            "suppliers/{$this->tenant_id}/machines/".$machine['object_id'] , [], [
                "machine" => $machine,
                "options" => $options,
                "tenant_name" => tenant()?->hostnames->first()?->fqdn
            ], forceJson: true);
    }

    /**
     * @param string $machine_id
     * @return string
     * @throws GuzzleException
     */
    final public function obtainDeleteMachine(
        string $machine_id,
    )
    {
        return $this->makeRequest('delete',
            "suppliers/{$this->tenant_id}/machines/".$machine_id);
    }

    /**
     * @param array  $catalogue
     * @param string $tenant_name
     * @return string
     * @throws GuzzleException
     */
    final public function obtainStoreCatalogue(
        array $catalogue,
        string $tenant_name
    )
    {
        return $this->makeRequest('post',
            "suppliers/{$this->tenant_id}/catalogues", [], [
                "catalogue" => $catalogue,
                "tenant_name" => $tenant_name
            ]);
    }

    /**
     * @param array  $catalogue
     * @param string $tenant_name
     * @return string
     * @throws GuzzleException
     */
    final public function obtainUpdateCatalogue(
        array $catalogue,
        string $tenant_name
    )
    {
        return $this->makeRequest('put',
            "suppliers/{$this->tenant_id}/catalogues/".$catalogue['object_id'], [], [
                "catalogue" => $catalogue,
                "tenant_name" => $tenant_name
            ]);
    }

    /**
     * @param array  $catalogue
     * @param string $tenant_name
     * @return string
     * @throws GuzzleException
     */
    final public function obtainDeleteCatalogue(
        array $catalogue,
        string $tenant_name
    )
    {
        return $this->makeRequest('delete',
            "suppliers/{$this->tenant_id}/catalogues/".$catalogue['object_id'], []);
    }
}

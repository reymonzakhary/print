<?php

namespace App\Services\Tenant\Calculations;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class CalculationService extends ServiceContract
{
    use ConsumesExternalServices;


    /**
     * CategoryService constructor.
     */
    public function __construct()
    {
        $this->base_uri = config('services.calculation.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
    }

    /**
     * @param array $request
     * @param string|null $calculation_type
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainProductItems(
        array  $request,
        null|string $calculation_type = null,
    ): array|string
    {
        return $this->makeRequest('post',
            "suppliers/{$this->tenant_id}/products/items", formParams: [
                "calculation_type" => $calculation_type,
                "products" => $request
            ], forceJson: true);
    }


    /**
     * @param string $category
     * @param array  $filter
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCalculatedPrices(
        string $category,
        array  $filter
    ): array|string
    {
        return $this->makeRequest('post',
            "suppliers/{$this->tenant_id}/categories/{$category}/products/calculate/price", formParams: $filter, forceJson: true);
    }


    /**
     * @param string $category
     * @param array $filter
     * @param array $queryParams
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCalculatedShopPrices(
        string $category,
        array  $filter,
        array $queryParams = [],
    ): array|string
    {
        return $this->makeRequest('post',
            "shop/suppliers/{$this->tenant_id}/categories/{$category}/products/calculate/price", queryParams: $queryParams, formParams: $filter, forceJson: true);
    }

    /**
     * @param string $category
     * @param array $filter
     * @param array $queryParams
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCalculatedShopPriceList(
        string $category,
        array  $filter,
        array $queryParams = [],
    ): array|string
    {
        return $this->makeRequest('post',
            "shop/suppliers/{$this->tenant_id}/categories/{$category}/products/calculate/price/list", queryParams: $queryParams, formParams: $filter, forceJson: true);
    }

    /**
     * @param string $category
     * @param array  $filter
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainSemiCalculatedPrices(
        string $category,
        array  $filter
    ): array|string
    {
        return $this->makeRequest('post',
            "suppliers/{$this->tenant_id}/categories/{$category}/products/calculate/price/semi", formParams: $filter, forceJson: true);
    }

    /**
     * @param string $category
     * @param array  $filter
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainSemiCalculatedShopPrices(
        string $category,
        array  $filter
    ): array|string
    {
        return $this->makeRequest('post',
            "shop/suppliers/{$this->tenant_id}/categories/{$category}/products/calculate/price/semi", formParams: $filter, forceJson: true);
    }

        /**
     * @param string $category
     * @param array  $filter
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainSemiCalculatedShopPriceList(
        string $category,
        array  $filter
    ): array|string
    {
        return $this->makeRequest('post',
            "shop/suppliers/{$this->tenant_id}/categories/{$category}/products/calculate/price/semi/list", formParams: $filter, forceJson: true);
    }


}

<?php


namespace App\Services\Categories\Products\Prices;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;

class PriceCalculationService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.assortments.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
        $this->tenant_name = optional(request()->hostname)->fqdn;

    }

    final public function obtainCalculatedPrices(
        string $category,
        array  $request
    )
    {
        $request['tenant_name'] = $this->tenant_name;
        return $this->makeRequest('post',
            "suppliers/{$this->tenant_id}/categories/{$category}/calculate/prices", [], $request, [], false, true);
    }

    final public function obtainStorePrices(
        string $category,
        string $combination,
        array $filter
    )
    {
        return $this->makeRequest('post',
            "prices/{$this->tenant_id}/{$category}/{$combination}", [], $filter);
    }

    final public function obtainStoreCombinationPrices(string $category, string $uuid): array|string
    {
        $this->base_uri = config('services.prices.base_uri');
        return $this->makeRequest('post',
            "suppliers/{$uuid}/categories/{$category}/price/buffer", [],
            [
                'host_id' => request()->hostname->host_id,
                'supplier_name' => $this->tenant_name,
            ], [], false, true);
    }
}

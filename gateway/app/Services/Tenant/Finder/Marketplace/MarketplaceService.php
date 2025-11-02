<?php


namespace App\Services\Tenant\Finder\Marketplace;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class MarketplaceService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * CategoryService constructor.
     */
    public function __construct()
    {
        $this->base_uri = config('services.marketplace.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
    }

    /**
     * Obtain finder search categories.
     *
     * @param string $linked
     * @return mixed
     * @throws GuzzleException
     */
    final public function obtainMarketplaceCategories(
        string $linked
    ): mixed
    {
        return $this->makeRequest(method:'get',requestUrl:  "manifest-merged-boops",formParams: [
            "tenant_id" => $this->tenant_id,
            "sku" => $linked,
        ], forceJson: true);
    }

    /**
     * Obtain a single marketplace category by linked ID AND tenant ID.
     *
     * @param string $linked
     * @return mixed
     * @throws GuzzleException
     */
    final public function obtainMarketplaceCategoriesById(
        string $linked
    ): mixed
    {
        return $this->makeRequest(method:'post', requestUrl: "manifest-merged-boops-by-linked", formParams: [
            "tenant_id" => $this->tenant_id,
            "linked" => $linked,
        ], forceJson: true);
    }

}

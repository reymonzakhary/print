<?php


namespace App\Services\Tenant\Finder\Categories\Products;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class ProductService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.finder.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
    }

    /**
     * @param string $category
     * @param array  $filter
     * @param array  $body
     * @return string
     * @throws GuzzleException
     */
    final public function obtainProductsByFilter(
        string $category,
        array  $filter,
        array  $body
    )
    {
        $body['suppliers'][] = ['supplier_id' => tenant()->uuid, 'host_id' => domain()->host_id];
        $body['me'] = ['supplier_id' => tenant()->uuid, 'host_id' => domain()->host_id];
        return $this->makeRequest('post',
            "products/{$category}", $filter, $body, [], false, true
        );
    }

    /**
     * @param string $category
     * @return string
     * @throws GuzzleException
     */
    final public function obtainProductsShop(
        string $category,
    )
    {
        return $this->makeRequest('post',
            "products/shop/{$category}", [], request()->merge([
                'tenant_id' => request()->tenant->uuid
            ])->toArray(), [], false, true);
    }
}

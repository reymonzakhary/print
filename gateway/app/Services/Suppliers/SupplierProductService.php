<?php


namespace App\Services\Suppliers;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class SupplierProductService extends ServiceContract
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

    /**
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainProducts(
        string $category,
        array  $request
    )
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/categories/{$category}/products", $request);
    }

    /**
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainProductsFillter(
        string $category,
        array  $request
    )
    {
        return $this->makeRequest('post', "suppliers/{$this->tenant_id}/categories/{$category}/products", [], $request);
    }

    /**
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainGenerateProducts(
        string $category,
        array  $request,
        string $tenant,
        string $host_id
    )
    {
        $request['tenant_name'] = $tenant;
        $request['host_id'] = $host_id;
        return $this->makeRequest('post', "suppliers/{$tenant}/categories/{$category}/products/generate", [], $request, [], false, true);
    }

    /**
     * @param string $category
     * @param string $tenant
     * @return string
     * @throws GuzzleException
     */
    final public function obtainReGenerateProducts(
        string $category,
        string $tenant
    )
    {
        return $this->makeRequest('post', "suppliers/{$tenant}/categories/{$category}/products/regenerate");
    }

    /**
     * @param string $category
     * @param string $tenant
     * @return string
     * @throws GuzzleException
     */
    final public function obtainCountGeneratedProducts(
        string $category,
        string $tenant
    )
    {
        return $this->makeRequest('get', "suppliers/{$tenant}/categories/{$category}/products/count");
    }

    /**
     * @param string $tenant
     * @param string $category
     * @param        $type
     * @return string
     * @throws GuzzleException
     */
    final public function exportCategory(
        string $tenant,
        string $category,
               $type
    )
    {
        return $this->makeRequest('post', "suppliers/{$tenant}/categories/{$category}/export", [], $type, [], false, true);
    }

    /**
     * @param string $tenant
     * @param string $category
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    public function importCategory(
        string $tenant,
        string $category,
        array  $request
    )
    {
        $request['tenant_name'] = $this->tenant_name;
        $request['host_id'] = request()->hostname->host_id;
        if (isset($request['runs']) && $request['runs'] == 'true') {
            return $this->makeRequest('post', "suppliers/{$tenant}/categories/{$category}/import/runs", [], $request, [], false, true);
        }
        return $this->makeRequest('post', "suppliers/{$tenant}/categories/{$category}/import", [], $request, [], false, true);
    }
}

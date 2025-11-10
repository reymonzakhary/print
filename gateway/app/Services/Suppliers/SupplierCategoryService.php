<?php


namespace App\Services\Suppliers;


use App\Contracts\ServiceContract;
use App\Models\Tenant\Language;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;
use LogicException;
use RuntimeException;

class SupplierCategoryService extends ServiceContract
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
     * @param string $tenant
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCategoriesCount(
        string $tenant,
    ):string|array
    {
        return $this->makeRequest('get', "suppliers/{$tenant}/categories/count", forceJson: true);
    }


    /**
     * @param string $tenant
     * @param array  $request
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCategories(
        string $tenant,
        array  $request
    ):string|array
    {
        return $this->makeRequest('get', "suppliers/{$tenant}/categories", $request);
    }

    /**
     * @param string $tenant
     * @param array  $request
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainPublishedCategories(
        string $tenant,
        array  $request,
    ):string|array
    {
        return $this->makeRequest('get', "suppliers/{$tenant}/categories", array_merge($request, [
            'published' => true,
        ]));
    }

    /**
     * @param string $category
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCategory(
        string $category,
    ): string|array
    {
        return $this->makeRequest('get', "suppliers/" . tenant()->uuid . "/categories/{$category}");
    }

    /**
     * @param string $supplier
     * @param string $category
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainSingleCategory(
        string $supplier,
        string $category
    ): string|array|null
    {
        return $this->makeRequest('get', "suppliers/{$supplier}/categories/{$category}");
    }

    /**
     * @param array $request
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainStoreCategory(
        array $request
    ):string|array
    {
        $request['tenant_name'] = $this->tenant_name;
        $request['lang'] = Language::pluck('iso');
        return $this->makeRequest('post', "suppliers/{$this->tenant_id}/categories", [], $request, [], false, true);
    }

    /**
     * @param array  $request
     * @param string $slug
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainUpdateCategory(
        array  $request,
        string $slug
    ): string|array
    {
        $request['tenant_name'] = $this->tenant_name;
        return $this->makeRequest('put', "suppliers/{$this->tenant_id}/categories/{$slug}", [], $request, [], false, true);
    }

    /**
     * @param string $supplier
     * @param string $category
     * @param array  $request
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainLinkCategorySupplier(
        array  $request,
        string $supplier,
        string $category
    ): string|array|null
    {
        return $this->makeRequest('post', "suppliers/{$supplier}/categories/{$category}/link", [], [
            'tenant_id' => $this->tenant_id,
            'tenant_name' => $this->tenant_name,
            'name' => optional($request)['name'],
            'lang' => Language::pluck('iso'),
            'iso' => app()->getLocale(),
        ], [], false, true);
    }

    /**
     * @param string $category
     * @param string $tenant_id
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainDeletedSupplierCategory(
        string $category,
        string $tenant_id
    ): string|array
    {
        return $this->makeRequest('delete', "suppliers/{$tenant_id}/categories/{$category}");
    }

    /**
     * @param string $category
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCategoryObject(
        string $category
    ): string|array
    {
        return $this->makeRequest('get',"/suppliers/{$this->tenant_id}/categories/{$category}/object");
    }

    /**
     * @param array $categoryData
     *
     * @return void
     *
     * @throws GuzzleException
     */
    public function handleExternalCategoryData(
        array $categoryData,
    ): void
    {
        $this->makeRequest(
            method: 'post',
            requestUrl: sprintf('/import/suppliers/%s/categories', $this->tenant_id),
            formParams: array_merge(
                $categoryData,
                [
                    'tenant_id' => $this->tenant_id,
                    'tenant_name' => $this->tenant_name,
                ]
            ),
            forceJson: true
        );
    }
}

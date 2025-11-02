<?php

declare(strict_types=1);

namespace App\Services\Tenant\Categories;

use App\Contracts\ServiceContract;
use App\Models\Tenants\Language;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class SupplierCategoryService
 *
 * This class handles the communication with the supplier's category API.
 * @author Reymon Zakhary
 */
class SupplierCategoryService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->base_uri = config('services.supplier_categories.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid??tenant()->uuid;
        $this->tenant_name = optional(request()->hostname)->fqdn??tenant()?->hostname?->fqdn;
    }

    /**
     * Obtain the categories from the supplier.
     *
     * @param array $request The optional request parameters.
     *
     * @return string|array The response from the API call.
     * @throws GuzzleException
     */
    final public function obtainCategories(
        array $request = []
    ): string|array
    {
        return $this->makeRequest(
            method: 'get',
            requestUrl: "suppliers/{$this->tenant_id}/categories",
            queryParams: $request
        );
    }

    /**
     * Obtain the categories from the supplier.
     *
     *
     * @return string|array The response from the API call.
     * @throws GuzzleException
     */
    final public function obtainSharedCategoriesCount(
    ): string|array
    {
        return $this->makeRequest(
            method: 'get',
            requestUrl: "suppliers/{$this->tenant_id}/categories/shared",
        );
    }

    final public function obtainCategory(
        string $category
    )
    {
        return $this->makeRequest(
            method: 'get',
            requestUrl: "suppliers/{$this->tenant_id}/categories/{$category}",
            forceJson: true
        );
    }

    /**
     * @param string $linked_id
     * @param bool $shared
     * @return array|object|string|null
     * @throws GuzzleException
     */
    public function obtainCategoriesByLink(
        string $linked_id,
        bool $shared
    ): object|array|string|null
    {
        return $this->makeRequest(
            method: 'post',
            requestUrl: "suppliers/{$this->tenant_id}/categories/linked/{$linked_id}",
            formParams: [
                "shared" => $shared
            ],
            forceJson: true
        );
    }

    public function obtainMyCategoriesByLink(
        string $linked_id
    ): object|array|string|null
    {
        return $this->makeRequest(
            method: 'get',
            requestUrl: "suppliers/{$this->tenant_id}/linked/{$linked_id}/categories",
            forceJson: true
        );
    }

    /**
     * Store a new category.
     *
     * @param array $request The request data.
     *
     * @return string|array The response data.
     * @throws GuzzleException
     */
    final public function storeCategory(
        array $request = []
    ): string|array
    {
        return $this->makeRequest(
            method: 'post',
            requestUrl: "suppliers/{$this->tenant_id}/categories",
            formParams: array_merge(
                [
                  'language' => Language::pluck('iso')->toJson(),
                  'tenant_name' => $this->tenant_name,
                ],
                $request
            ),
            forceJson: true
        );
    }

    /**
     * @param array $request
     * @param string $slug
     * @return string|array
     * @throws GuzzleException
     */
    final public function updateCategory(
        array  $request,
        string $slug
    ): string|array
    {
        $request['tenant_name'] = $this->tenant_name;
        return $this->makeRequest(
            method: 'put',
            requestUrl: "suppliers/{$this->tenant_id}/categories/{$slug}",
            formParams: array_merge(
                [
                    'iso' => app()->getLocale(),
                    'tenant_name' => $this->tenant_name,
                ],
                $request
            ),
            forceJson: true
        );
    }

    final public function deleteCategory(
        string $category,
        string $tenant_id
    ): string|array
    {
        return $this->makeRequest(
            method: 'delete',
            requestUrl: "suppliers/{$tenant_id}/categories/{$category}",
            forceJson: true
        );
    }

    final public function updateCategoryBoops(
        array  $request,
        string $slug
    ){
        $request['tenant_name'] = $this->tenant_name;

        return $this->makeRequest(
            method: 'put',
            requestUrl: "suppliers/{$this->tenant_id}/categories/{$slug}/boops",
            formParams: array_merge(
                [
                    'iso' => app()->getLocale(),
                    'tenant_name' => $this->tenant_name,
                ],
                $request
            ),
            forceJson: true
        );
    }

    /**
     * Remove a Category Media FIle.
     *
     * @param string $category The selected category.
     * @param string $filename The media filename to remove.
     * @return string|array The response data.
     * @throws GuzzleException
     */

    final public function removeCategoryMedia(
        string $category , string $filename
    ): array|string
    {
        return $this->makeRequest(
            method: 'delete',
            requestUrl: "suppliers/{$this->tenant_id}/categories/{$category}/media",
            formParams: array_merge(
                [
                    'iso' => app()->getLocale(),
                    'tenant_name' => $this->tenant_name,
                    'filename' => $filename,
                ],
            ),
            forceJson: true
        );
    }
}

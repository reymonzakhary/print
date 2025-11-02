<?php


namespace App\Services\System\Categories;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class CategoryService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->base_uri = config('services.categories.base_uri');
    }

    /**
     * @param string $category
     * @return string|array|null
     * @throws GuzzleException
     */
    final public function obtainSystemCategoryManifest(
        string $category
    ): string|array|null
    {
        return $this->makeRequest('get', "/categories/{$category}/manifest");
    }

    /**
     * @param array $request
     * @param string $category
     * @return string|array
     * @throws GuzzleException
     */
    final public function storeSystemCategoryManifest(
        array $request,
        string $category
    ): string|array
    {
        return $this->makeRequest(method:'post',requestUrl:"/categories/{$category}/manifest", formParams: $request);
    }

    /**
     * @param array $request
     * @param string $category
     * @return string|array
     * @throws GuzzleException
     */
    final public function updateSystemCategoryManifest(
        array $request,
        string $category
    ): string|array
    {
        return $this->makeRequest(method:'put',requestUrl:"/categories/{$category}/manifest", formParams: $request);
    }

    /**
     * @param string $category
     * @param string $supplier_id
     * @return string|array|null
     * @throws GuzzleException
     */
    final public function obtainLinkedSupplierManifest(
        string $category,
        string $supplier_id,
    ): string|array|null
    {
        return $this->makeRequest(method:'get',requestUrl:"/categories/{$category}/manifest/{$supplier_id}/linked");
    }

    final public function obtainSupplierManifest(
        string $category,
        string $supplier_id,
    ): string|array|null
    {
        return $this->makeRequest(method:'get',requestUrl:"/categories/{$category}/manifest/{$supplier_id}");
    }

    /**
     * @param array $array
     * @return string
     * @throws GuzzleException
     */
    final public function obtainSystemCategories(
        array $array
    )
    {
        return $this->makeRequest('get', "/categories", $array);
    }


    /**
     * @param string $linked
     * @return array
     */
    final public function obtainLinkedCategoriesSuppliers(
        string $linked,
    )
    {
        return $this->makeRequest('get', "/categories/{$linked}/suppliers");
    }

        /**
     * @param string $linked
     * @param string $supplier_id
     * @return array
     */
    final public function obtainLinkedCategoriesManifest(
        string $linked,
        string $supplier_id,
    )
    {
        return $this->makeRequest('post', "/categories/{$linked}/suppliers/{$supplier_id}/manifest/load");
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainSystemCategory(
        string $category
    )
    {
        return $this->makeRequest('get', "/categories/{$category}");
    }

    /**
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function storeSystemCategory(
        array $request
    )
    {
        return $this->makeRequest('post', "categories", [], $request);
    }

    /**
     * @param string $category
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function updateSystemCategory(
        string $category,
        array  $request
    )
    {
        return $this->makeRequest('put', "categories/{$category}", [], $request);
    }

    /**
     * @param array  $request
     * @param string $category
     * @return string
     * @throws GuzzleException
     */
    final public function deleteSystemCategory(
        string $category,
        array  $request
    )
    {
        return $this->makeRequest('delete', "categories/{$category}", $request);
    }

    /**
     * @param string $slug
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainAttachSystemCategories(
        string $slug,
        array  $request
    )
    {
        return $this->makeRequest('post', "categories/{$slug}/attach", [], $request);
    }

    /**
     * @param string $slug
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainDetachSystemCategories(
        string $slug,
        array  $request
    )
    {
        return $this->makeRequest('post', "categories/{$slug}/detach", [], $request);
    }


    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainUnmatchedSystemCategories()
    {
        return $this->makeRequest('get', "unmatched/categories");
    }

    /**
     * Delete unmatched system category.
     *
     * @param string $category
     * @return object|array|string|null
     * @throws GuzzleException
     */
    final public function deleteUnmatchedSystemCategory(
        string $category
    ): object|array|string|null
    {
        return $this->makeRequest('delete', "unmatched/categories/{$category}");
    }


    /**
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainMatchedSystemCategories(): string|array
    {
        return $this->makeRequest('get', "matched/categories");
    }

    /**
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainUnlinkedCategories(
        array $request
    )
    {
        return $this->makeRequest("GET", "unlinked/categories", $request);
    }

    /**
     * @param array $params
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function mergeSystemCategories(
        array $request,
        array $params = []
    )
    {
        return $this->makeRequest("POST", "merge/categories", $params, $request);
    }

}

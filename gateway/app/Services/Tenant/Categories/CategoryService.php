<?php


namespace App\Services\Tenant\Categories;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class CategoryService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * CategoryService constructor.
     */
    public function __construct()
    {
        $this->base_uri = config('services.assortments.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
    }

    final public function getCategories(
        array $categories,
    )
    {
        return $this->makeRequest(
            'post',
            "suppliers/{$this->tenant_id}/categories/attached",
            formParams: $categories,
            forceJson: true
        );
    }

    /**
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCategories(): string|array
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/categories", request()->all());
    }

    /**
     * @param array $params
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainSearchCategories(
        array $params
    ): string|array
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/categories/search", $params);
    }

    /**
     * @param string $category
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCategory(
        string $category
    ): string|array
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/categories/{$category}");
    }

}

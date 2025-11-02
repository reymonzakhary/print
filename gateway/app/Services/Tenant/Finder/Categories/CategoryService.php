<?php


namespace App\Services\Tenant\Finder\Categories;


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
        $this->base_uri = config('services.finder.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainFinderCategories()
    {
        return $this->makeRequest('get', "categories", request()->all());
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainFinderSearchCategories(
        array $params
    )
    {
        return $this->makeRequest('get', "categories/search", queryParams: $params, forceJson: true);
    }

    /**
     * @param string $category
     * @return string
     * @throws GuzzleException
     */
    final public function obtainFinderCategory(
        string $category
    )
    {
        return $this->makeRequest('get', "categories/{$category}");
    }
}

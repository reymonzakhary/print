<?php


namespace App\Services\Tenant\Finder\Categories;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class SearchService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * CategoryService constructor.
     */
    public function __construct()
    {
        $this->base_uri = config('services.search.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
    }

    /**
     * Obtain finder search categories.
     *
     * @param array $params
     * @return mixed
     * @throws GuzzleException
     */
    final public function obtainFinderSearchCategories(
        array $params
    ): mixed
    {

        return $this->makeRequest(method:'get',requestUrl:  "category-search",queryParams:  $params);
    }

}

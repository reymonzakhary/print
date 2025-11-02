<?php


namespace App\Services\Categories;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;


class CategoryService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.assortments.base_uri');
        $this->tenant_id = optional(tenant())->uuid;
        $this->tenant_name = optional(request()->hostname)->fqdn;
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainFinderCategories()
    {
        return $this->makeRequest('get', '/categories');
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainSearchCategories()
    {
        return $this->makeRequest('post', '/categories');
    }

}

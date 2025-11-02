<?php


namespace App\Services\Tenant\Categories;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;


class BoxService extends ServiceContract
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
     * @return string
     * @throws GuzzleException
     */
    final public function obtainFinderCategoryBoxes(
        string $category
    )
    {
        return $this->makeRequest('get', "{$this->tenant_id}/categories/{$category}/boxes");
    }

    /**
     * @param array $params
     * @return string
     * @throws GuzzleException
     */
    final public function obtainFinderSearchBoxes(
        array $params
    )
    {
        return $this->makeRequest('get', "boxes/search", $params);
    }

}

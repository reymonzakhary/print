<?php


namespace App\Services\Tenant\Categories;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;


class OptionService extends ServiceContract
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
     * @param string $box
     * @return string
     * @throws GuzzleException
     */
    final public function obtainFinderBoxOptions(
        string $category,
        string $box
    )
    {
        return $this->makeRequest('get', "{$this->tenant_id}/categories/{$category}/boxes/{$box}/options");
    }


    /**
     * @param string $category
     * @param string $box
     * @return string
     * @throws GuzzleException
     */
    final public function obtainFinderSearchOption(
        array $request,
    )
    {
        return $this->makeRequest('get', "options/search", $request);
    }
}

<?php


namespace App\Services\Categories;


use App\Contracts\ServiceContract;
use App\Models\Tenants\Language;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;


class BoopsService extends ServiceContract
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
     * @param string $category
     * @return string
     * @throws GuzzleException
     */
    final public function obtainCategoryBoops(
        string $category
    )
    {
        return $this->makeRequest('get',"/suppliers/{$this->tenant_id}/categories/{$category}/boops");
    }

    /**
     * @param string $category
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainCreateCategoryBoops(
        string $category,
        array  $request
    )
    {
        $request['tenant_name'] = $this->tenant_name;
        $request['lang'] = Language::all()->toArray();
        $request['sys_iso'] = app()->getLocale();

        return $this->makeRequest('post', "suppliers/{$this->tenant_id}/categories/{$category}/boops", [], $request, [], false, true);
    }

    /**
     * @param string $category
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainUpdateCategoryBoops(
        string $category,
        array  $request
    )
    {
        $request['tenant_name'] = $this->tenant_name;
        $request['lang'] = Language::all()->toArray();
        $request['sys_iso'] = app()->getLocale();

        return $this->makeRequest('put', "suppliers/{$this->tenant_id}/categories/{$category}/boops", [], $request, [], false, true);
    }

    /**
     * @param string $category
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainOpenProductCategoryBoops(
        string $category,
        array  $request
    )
    {
        $request['tenant_name'] = $this->tenant_name;
        $request['lang'] = Language::all()->toArray();
        $request['sys_iso'] = app()->getLocale();

        return $this->makeRequest('put', "suppliers/{$this->tenant_id}/categories/{$category}/boops/open-product", [], $request, [], false, true);
    }

}

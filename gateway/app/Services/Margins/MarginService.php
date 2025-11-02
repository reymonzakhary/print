<?php


namespace App\Services\Margins;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class MarginService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * set margin base service uri
     */
    public function __construct()
    {
        $this->base_uri = config('services.margins.base_uri');
    }

    /**
     * @param string $tenant
     * @return string
     * @throws GuzzleException
     */
    final public function obtainMargin(
        string $tenant
    )
    {
        return $this->makeRequest(
            'get',
            "margins/suppliers/{$tenant}/general"
        );
    }

    /**
     * @param string $tenant
     * @param array $margins
     * @return string
     * @throws GuzzleException
     */
    public function obtainUpdateGeneralMargin(
        string $tenant,
        array  $margins
    )
    {
        return $this->makeRequest('patch',
            "margins/suppliers/{$tenant}/general", formParams: $margins, forceJson: true);
    }

    /**
     * @param string $tenant
     * @param string $category
     * @return string
     * @throws GuzzleException
     */
    final public function obtainCategoryMargin(
        string $tenant,
        string $category
    )
    {
        return $this->makeRequest('get',
            "margins/tenants/{$tenant}/categories/{$category}");
    }

    /**
     * @param string $tenant
     * @param string $category
     * @param array  $margins
     * @return string
     * @throws GuzzleException
     */
    public function obtainUpdatedCategoryMargin(
        string $tenant,
        string $category,
        array  $margins
    )
    {
        return $this->makeRequest('patch',
            "margins/tenants/{$tenant}/categories/{$category}", formParams: $margins, forceJson: true);
    }
}

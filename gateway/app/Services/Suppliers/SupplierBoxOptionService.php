<?php


namespace App\Services\Suppliers;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;



/**
 * @deprecated This script is deprecated and will be removed in a future version.
 *             Please migrate to the Options\OptionService.php.
 */

class SupplierBoxOptionService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.assortments.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
        $this->tenant_name = optional(request()->hostname)->fqdn;
    }

    final public function obtainOptions(string $box, array $request)
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/boxes/{$box}/options", $request);
    }

    final public function obtainOption(string $category, string $option, array $request)
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/categories/{$category}/options/{$option}", $request);
    }


    final public function obtainStoreOption(
        array $body
    )
    {
        $body['tenant_name'] = $this->tenant_name;
        return $this->makeRequest('post', "suppliers/{$this->tenant_id}/options", [], $body, [], false, true);
    }

    final public function obtainStoreBoxOption(
        string $category,
        array  $body
    )
    {
        $body['tenant_name'] = $this->tenant_name;
        return $this->makeRequest('post', "suppliers/{$this->tenant_id}/categories/{$category}/options", [], $body, [], false, true);
    }

    final public function obtainUpdateOption(
        array  $body,
        string $category,
        string $option
    )
    {
        $body['tenant_name'] = $this->tenant_name;
        $body['iso'] = app()->getLocale();

        return $this->makeRequest('put', "suppliers/{$this->tenant_id}/categories/{$category}/options/{$option}", [], $body, [], false, true);
    }

}

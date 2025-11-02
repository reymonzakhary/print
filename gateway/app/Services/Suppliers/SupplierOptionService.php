<?php


namespace App\Services\Suppliers;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;


/**
 * @deprecated This script is deprecated and will be removed in a future version.
 *             Please migrate to the Options\OptionService.php.
 */
class SupplierOptionService extends ServiceContract
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

    final public function obtainOptions(
        array $request
    )
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/options", $request);
    }

    /**
     * @throws GuzzleException
     */
    public function obtainOption(
        string $option
    ): array|string
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/options/$option");
    }

    final public function obtainStoreOption(
        array $body,
    )
    {
        $body['tenant_name'] = $this->tenant_name;
        return $this->makeRequest('post', "suppliers/{$this->tenant_id}/options", [], $body, [], false, true);
    }


    final public function obtainUpdateOption(
        array  $body,
        string $option
    )
    {
        $body['tenant_name'] = $this->tenant_name;
        $body['iso'] = app()->getLocale();
        return $this->makeRequest('put', "suppliers/{$this->tenant_id}/options/$option", [], $body, [], false, true);
    }

    // TODO : Delete option completely from the supplier database is disabled for now
//    final public function obtainDeleteOption(
//        array  $body,
//        string $option
//    )
//    {
//        $body['tenant_name'] = $this->tenant_name;
//        $body['iso'] = app()->getLocale();
//        return $this->makeRequest('delete', "suppliers/{$this->tenant_id}/options/$option", [], $body, [], false, true);
//    }

    /**
     * @param string|null $machine_id
     * @return array|string
     * @throws GuzzleException
     */
    final public function obtainMachineOptions(
        null|string $machine_id
    ): array|string
    {
        return $machine_id ?
            $this->makeRequest('get', "suppliers/{$this->tenant_id}/machines/".$machine_id.'/options'):
            [];
    }


    final public function obtainCategoryOptions(
        array $body,
        string $categoryId
    )
    {
        $body['tenant_name'] = $this->tenant_name;
        $body['category_id'] =  $categoryId;
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/categories/{$categoryId}/options", [], $body, [], false, true);
    }


    final public function obtainCategoryOption(
        array $body,
        string $categoryId,
        string $optionId
    ): object|array|string|null
    {
        $body['tenant_name'] = $this->tenant_name;
        $body['category_id'] =  $categoryId;
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/categories/{$categoryId}/options/{$optionId}", [], $body, [], false, true);
    }

    final public function obtainUpdateCategoryOption(
        array $body,
        string $categoryId,
        string $optionId
    ): object|array|string|null
    {
        $body['tenant_name'] = $this->tenant_name;
        $body['category_id'] =  $categoryId;
        return $this->makeRequest('put', "suppliers/{$this->tenant_id}/categories/{$categoryId}/options/{$optionId}", [], $body, [], false, true);
    }


    final public function obtainStoreCategoryOption(
        array $body,
        string $categoryId,
        string $optionId
    ): object|array|string|null
    {
        $body['tenant_name'] = $this->tenant_name;
        $body['category_id'] =  $categoryId;
        return $this->makeRequest('post', "suppliers/{$this->tenant_id}/categories/{$categoryId}/options/{$optionId}", [], $body, [], false, true);
    }


    final public function obtainDeleteCategoryOption(
        string $categoryId,
        string $optionId
    ): object|array|string|null
    {
        $body['tenant_name'] = $this->tenant_name;
        $body['category_id'] =  $categoryId;
        return $this->makeRequest('delete', "suppliers/{$this->tenant_id}/categories/{$categoryId}/options/{$optionId}", [], $body, [], false, true);
    }


}

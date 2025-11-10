<?php


namespace App\Services\Suppliers;


use App\Contracts\ServiceContract;
use App\Models\Tenant\Language;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;


class SupplierBoxService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.boxes.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
        $this->tenant_name = optional(request()->hostname)->fqdn;
    }

    final public function obtainBoxes($request)
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/boxes", $request);
    }


    /**
     * Obtain a specific box for the current tenant
     *
     * @param string $box
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainBox(
        string $box
    ):string|array
    {
        return $this->makeRequest('get', "suppliers/{$this->tenant_id}/boxes/{$box}");
    }

    final public function obtainStoreBox(
        array $body,
        array $options
    )
    {
        $body['tenant_name'] = $this->tenant_name;
        $body['lang'] = Language::pluck('iso');
        return $this->makeRequest('post', "suppliers/{$this->tenant_id}/boxes", $options, $body, [], false, true);
    }

    final public function obtainUpdateBox(
        array  $body,
        string $box
    )
    {
        $body['tenant_name'] = $this->tenant_name;
        $body['lang'] = Language::pluck('iso');
        return $this->makeRequest('put', "suppliers/{$this->tenant_id}/boxes/$box", [], $body, [], false, true);
    }

}

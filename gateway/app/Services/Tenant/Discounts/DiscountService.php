<?php


namespace App\Services\Tenant\Discounts;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class DiscountService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * CategoryService constructor.
     */
    public function __construct()
    {
        $this->base_uri = config('services.discounts.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
    }


    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainSupplierDiscounts()
    {
        return $this->makeRequest('get', "{$this->tenant_id}/categories", request()->all());
    }

}

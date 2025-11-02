<?php


namespace App\Services\Suppliers;


use App\Contracts\ServiceContract;

class SupplierService extends ServiceContract
{

    /**
     *
     */
    public function __construct()
    {
        $this->baseUri = config('services.suppliers.base_uri');
    }
}

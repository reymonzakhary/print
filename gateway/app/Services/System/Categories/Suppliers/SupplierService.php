<?php


namespace App\Services\System\Categories\Suppliers;


use App\Utilities\Traits\ConsumesExternalServices;

class SupplierService
{
    use ConsumesExternalServices;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->base_uri = config('services.categories.base_uri');
    }

    final public function handelDetachCategory(
        string $category_slug,
        string $supplier_category_slug,
        array  $request
    )
    {
        return $this->makeRequest('post',
            "/categories/{$category_slug}/suppliers/{$supplier_category_slug}/detach",
            [],
            $request
        );
    }
}

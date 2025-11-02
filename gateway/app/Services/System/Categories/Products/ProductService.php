<?php


namespace App\Services\System\Categories\Products;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class ProductService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->base_uri = config('services.categories.base_uri');
    }

    /**
     * @param string $category_id
     * @return string
     * @throws GuzzleException
     */
    final public function obtainProductsByCategoryId(
        string $category_id
    )
    {
        return $this->makeRequest('get', "categories/{$category_id}");
    }

}

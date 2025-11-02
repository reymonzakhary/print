<?php


namespace App\Services\Categories;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;


class BoxService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.categories.base_uri');
    }

    final public function obtainCategoryBoxes(
        string $category
    )
    {
        return $this->makeRequest('get',
            "/categories/$category/boxes");
    }

    final public function matchBoxes(
        string $category,
        array  $body
    )
    {
        return $this->makeRequest('post',
            "/categories/$category/boxes/match",
            [],
            $body
        );
    }

    final public function storeBoxes(
        string $category,
        array  $body
    )
    {
        return $this->makeRequest('post',
            "/categories/$category/boxes",
            [],
            $body,
            [],
            true
        );
    }

    final public function updateBoxes(
        string $category,
        string $box,
        array  $body
    )
    {
        return $this->makeRequest('put',
            "/categories/$category/boxes/$box",
            [],
            $body,
            [],
            true
        );
    }

}

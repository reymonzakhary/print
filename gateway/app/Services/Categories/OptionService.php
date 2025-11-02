<?php


namespace App\Services\Categories;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;


/**
 * @deprecated This script is deprecated and will be removed in a future version.
 *             Please migrate to the Options\OptionService.php.
 */
class OptionService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.categories.base_uri');
    }

    final public function obtainBoxOptions(
        string $box
    )
    {
        return $this->makeRequest('get',
            "/categories/boxes/$box/options");
    }

    final public function matchOptions(
        string $box,
        array  $body
    )
    {
        return $this->makeRequest('post',
            "/categories/boxes/$box/options/match",
            [],
            $body);
    }

    final public function storeOption(
        string $box,
        array  $body
    )
    {
        return $this->makeRequest('post',
            "/categories/boxes/$box/options",
            [],
            $body,
            [],
            true);
    }

    final public function updateOption(
        string $box,
        string $option,
        array  $body
    )
    {
        return $this->makeRequest('put',
            "/categories/boxes/$box/options/$option",
            [],
            $body,
            [],
            true);
    }
}

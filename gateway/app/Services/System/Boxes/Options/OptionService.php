<?php


namespace App\Services\System\Boxes\Options;


use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class OptionService extends ServiceContract
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
     * @param string $box
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainOptionsByBox(
        string $box,
        array  $request
    )
    {
        return $this->makeRequest('get', "boxes/{$box}/options", $request);
    }

    /**
     * @param string $box
     * @param string $option
     * @return string
     * @throws GuzzleException
     */
    final public function obtainOptionByBox(
        string $box,
        string $option
    )
    {
        return $this->makeRequest('get', "boxes/{$box}/options/{$option}");
    }
}

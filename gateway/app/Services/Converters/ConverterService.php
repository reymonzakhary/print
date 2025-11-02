<?php

namespace App\Services\Converters;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class ConverterService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.converter.base_uri');
    }

    /**
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function convert(array $request)
    {
        return $this->makeRequest('post', '/screenshot/from_url', [], $request);
    }


}

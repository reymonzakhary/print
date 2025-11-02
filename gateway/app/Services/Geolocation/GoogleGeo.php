<?php

namespace App\Services\Geolocation;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Config;

class GoogleGeo  extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * @var float|int
     */
    private int|float $lat = 0;

    /**
     * @var float|int
     */
    private int|float $lng = 0;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = Config::get('services.geolocation.base_uri');
        $this->access_token = Config::get('services.geolocation.access_token');
    }

    /**
     * @param string|null $address
     * @return array|string
     * @throws GuzzleException
     */
    public function address(
        string $address = null
    ): array|string
    {
        return $this->makeRequest('POST', '/maps/api/geocode/json', [
            'address' => $address,
            'key' => $this->access_token
        ]);
    }

    /**
     * @param float|null $lat
     * @param float|null $lng
     * @return array|string
     * @throws GuzzleException
     */
    public function latLng(
        float $lat = null,
        float $lng = null
    ): array|string
    {
        return $this->makeRequest('POST', '/maps/api/geocode/json', [
            'latlng' => "{$lat},{$lng}",
            'key' => $this->access_token
        ]);
    }
}

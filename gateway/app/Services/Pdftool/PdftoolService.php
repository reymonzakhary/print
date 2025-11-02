<?php

namespace App\Services\Pdftool;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class PdftoolService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.pdf_tool.base_uri');
    }

    /**
     * @param array  $search
     * @param array  $replace
     * @param string $url
     * @param array  $args
     * @param bool   $sync
     * @return mixed
     * @throws GuzzleException
     */
    final public function findAndReplaceMultipleStrings(
        array  $search,
        array  $replace,
        string $url,
        array  $args = [],
        bool   $sync = false
    ): mixed
    {
        return $this->makeRequest(
            method: 'post',
            requestUrl: "/replace-text",
            formParams: [
                "url" => $url,
                "search" => $search,
                "replace" => $replace,
                'async' => $sync,
                ...$args
            ],
            headers: [
                "Content-Type" => "application/json"
            ],
            forceJson: true
        );
    }


    /**
     * @param string $origin
     * @param string $stamp
     * @param float  $x
     * @param float  $y
     * @param int    $page
     * @param array  $args
     * @param bool   $sync
     * @return mixed
     * @throws GuzzleException
     */
    final public function addLayerOnPosition(
        string  $origin,
        string  $stamp,
        float   $x,
        float   $y,
        int     $page,
        ?string $search = null,
        bool    $act = false,
        array   $args = [],
        bool    $sync = false
    ): mixed
    {
        return $this->makeRequest(
            method: 'post',
            requestUrl: "/add-layer-on-position",
            formParams: [
                "origin" => $origin,
                "stamp" => $stamp,
                "x" => $x,
                'y' => $y,
                'page' => $page,
                'search' => $search,
                'act' => $act,
                ...$args
            ],
            headers: [
                "Content-Type" => "application/json"
            ],
            forceJson: true
        );
    }

    /**
     * @param string $directory
     * @param string $disk
     * @param string $destinations
     * @param string $filename
     * @param bool   $separate
     * @return mixed
     * @throws GuzzleException
     */
    final public function mergePdfFiles(
        string $directory,
        string $disk,
        string $destinations,
        string $filename,
        bool   $separate = false,
    ): mixed
    {
        return $this->makeRequest(
            method: 'post',
            requestUrl: "/merge",
            formParams: [
                "directory" => $directory,
                "disk" => $disk,
                'destinations' => $destinations,
                'filename' => $filename,
                'separate' => $separate,
            ],
            headers: [
                "Content-Type" => "application/json"
            ],
            forceJson: true
        );
    }

    /**
     * @param array  $layers
     * @param string $templates
     * @param string $destination
     * @param string $disk
     * @return mixed
     * @throws GuzzleException
     */
    final public function addLayer(
        array $layers,
        string $templates,
        string $destination,
        string $disk,
    ): mixed
    {
        return $this->makeRequest(
            method: 'post',
            requestUrl: "/add-layer",
            formParams: [
                "layers" => $layers,
                "templates" => $templates,
                'destination' => $destination,
                'disk' => $disk
            ],
            headers: [
                "Content-Type" => "application/json"
            ],
            forceJson: true
        );
    }

    /**
     * @param array  $layers
     * @param string $templates
     * @param string $destination
     * @param string $disk
     * @return mixed
     * @throws GuzzleException
     */
    final public function addDynamicLayer(
        array $layers,
        string $templates,
        string $destination,
        string $disk,
    ): mixed
    {
        return $this->makeRequest(
            method: 'post',
            requestUrl: "/add-dynamic-layer",
            formParams: [
                "layers" => $layers,
                "templates" => $templates,
                'destination' => $destination,
                'disk' => $disk
            ],
            headers: [
                "Content-Type" => "application/json"
            ],
            forceJson: true
        );
    }

}

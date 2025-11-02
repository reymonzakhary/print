<?php

namespace App\Services\PdfCo;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;

class PdfCoService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.pdf_co.base_uri');
    }

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
            requestUrl: "/v1/pdf/edit/replace-text",
            formParams: [
                "url" => $url,
                "searchStrings" => $search,
                "replaceStrings" => $replace,
                'async' => $sync,
                ...$args
            ],
            headers: [
                "Content-Type" => "application/json",
                "x-api-key" => env('PDF_CO_TOKEN')
            ],
            forceJson: true
        );
    }

    final public function findAndDeleteMultipleStrings(
        array  $search,
        string $url,
        array  $args = []
    ): mixed
    {
        return $this->makeRequest(
            method: 'post',
            requestUrl: "/v1/pdf/edit/delete-text",
            formParams: [
                "url" => $url,
                "searchStrings" => $search,
                'async' => true,
                ...$args
            ],
            headers: [
                "Content-Type" => "application/json",
                "x-api-key" => env('PDF_CO_TOKEN')
            ],
            forceJson: true
        );
    }

    final public function getPosition(
        string $name,
        string $url,
        array  $args = []
    ): mixed
    {
        return $this->makeRequest(
            method: 'post',
            requestUrl: "https://api.pdf.co/v1/pdf/convert/to/json",
            formParams: [
                "name" => $name,
                "url" => $url,
                'async' => true,
                ...$args
            ],
            headers: [
                "Content-Type" => "application/json",
                "x-api-key" => env('PDF_CO_TOKEN')
            ],
            forceJson: true
        );

    }

    final public function check(
        string $jobId,
    ): mixed
    {
        return $this->makeRequest(
            method: 'post',
            requestUrl: "https://api.pdf.co/v1/job/check",
            formParams: [
                "jobid" => $jobId,
            ],
            headers: [
                "Content-Type" => "application/json",
                "x-api-key" => env('PDF_CO_TOKEN')
            ],
            forceJson: true
        );

    }
}

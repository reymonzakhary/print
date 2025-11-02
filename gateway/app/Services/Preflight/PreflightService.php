<?php

namespace App\Services\Preflight;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;

class PreflightService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     *
     */
    public function __construct()
    {
        $this->base_uri = config('services.preflight.base_uri');
    }


    public function obtainLowPdf($request)
    {
        $url = optional($request)['next'] ? $request['next'] : 'pdf';
        return $this->makeRequest('POST', '/api/' . $url, [], $request->toArray(), [], true);
    }

    public function obtainPdfStamp($request)
    {
        return $this->makeRequest('POST', '/api/pdf/stamp', [], $request->toArray(), [
            'Accept' => 'application/json'
        ], true);
    }
}

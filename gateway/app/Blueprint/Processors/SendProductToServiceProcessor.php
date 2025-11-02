<?php

namespace App\Blueprint\Processors;


use App\Services\Preflight\PreflightService;

class SendProductToServiceProcessor
{
    public PreflightService $preflightService;

    public function __construct(
        PreflightService $preflightService
    )
    {
        $this->preflightService = $preflightService;
    }

    public function handle($request)
    {
        return $this->preflightService->obtainLowPdf($request);
    }
}

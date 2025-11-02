<?php

namespace App\Blueprint\Processors;

use App\Http\Requests\Cart\CartStoreRequest;
use App\Services\pdfConverter\PdfConverterService;

class PdfConverterProcessor
{
    public PdfConverterService $pdfService;

    public function __construct(
        PdfConverterService $pdfService
    )
    {
        $this->pdfService = $pdfService;
    }

    public function handle($node, CartStoreRequest $request)
    {
        return $this->pdfService->obtainPdf($request);
    }


}

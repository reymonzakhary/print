<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Render;

use App\Actions\PriceAction\CalculationAction;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Quotation;
use App\Http\Resources\Quotations\QuotationResource;
use App\Utilities\Quotation\Generator\QuotationPdfGenerator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RenderPdfController extends Controller
{
    /**
     * @param Quotation $quotation
     * @param Request $request
     * @param QuotationPdfGenerator $pdfGenerator
     *
     * @return Response
     */
    public function __invoke(
        Quotation             $quotation,
        Request               $request,
        QuotationPdfGenerator $pdfGenerator,
    ): Response
    {
        return new Response(
            content: $pdfGenerator
                ->generate(
                    (new CalculationAction($quotation))->calculate(),
                    $request->user(),
                    hideExpirationMessage: true
                )
                ->output(),

            headers: [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf(
                    'inline; filename="quotation_%s_%s.pdf"',
                    $quotation->getAttribute('id'),
                    time()
                )
            ]
        );
    }
}

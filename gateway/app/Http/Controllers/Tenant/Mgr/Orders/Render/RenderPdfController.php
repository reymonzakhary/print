<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Orders\Render;

use App\Actions\PriceAction\CalculationAction;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Order;
use App\Utilities\Order\Generator\OrderPdfGenerator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RenderPdfController extends Controller
{
    /**
     * @param Order $order
     * @param Request $request
     * @param OrderPdfGenerator $pdfGenerator
     *
     * @return Response
     */
    public function __invoke(
        Order             $order,
        Request           $request,
        OrderPdfGenerator $pdfGenerator,
    ): Response
    {
        return new Response(
            content: $pdfGenerator
                ->generate(
                    (new CalculationAction($order))->calculate(),
                    $request->user(),
                    hideExpirationMessage: true
                )
                ->output(),

            headers: [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf(
                    'inline; filename="order_%s_%s.pdf"',
                    $order->getAttribute('id'),
                    time()
                )
            ]
        );
    }
}

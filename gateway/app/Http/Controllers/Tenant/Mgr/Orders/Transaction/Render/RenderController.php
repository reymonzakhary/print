<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Orders\Transaction\Render;

use App\Http\Controllers\Controller;
use App\Models\Tenants\Order;
use App\Models\Tenants\Transaction;
use App\Utilities\Order\Transaction\Generator\TransactionPdfGenerator;
use Illuminate\Http\Response;

/**
 * To Render the transaction as a specific type (PDF, Excel, Image, etc.)
 */
final class RenderController extends Controller
{
    /**
     * Render the transaction as a PDF file
     * 
     * @param Order $order
     * @param Transaction $transaction
     * @param TransactionPdfGenerator $generator
     *
     * @return Response
     */
    public function pdf(
        Order $order,
        Transaction $transaction,
        TransactionPdfGenerator $generator,
    ): Response
    {
        return new Response(
            content: $generator->generate($transaction)->output(), # binary data of the PDF
            headers: [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf(
                    'inline; filename="transaction_%s_%s_%s.pdf"',
                    $order->getAttribute('id'),
                    $transaction->getAttribute('id'),
                    uniqid(more_entropy: true)
                )
            ]
        );
    }
}

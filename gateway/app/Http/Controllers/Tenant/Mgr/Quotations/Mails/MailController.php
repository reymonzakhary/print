<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Mails;

use App\Actions\PriceAction\CalculationAction;
use App\Enums\OrderOrigin;
use App\Enums\Status;
use App\Events\Tenant\Quotation\QuotationAcceptedEvent;
use App\Events\Tenant\Quotation\QuotationRejectedEvent;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Quotation;
use App\Utilities\Quotation\QuotationHasher;
use Hyn\Tenancy\Environment;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

final class MailController extends Controller
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
        private readonly Environment $environment,
        private readonly QuotationHasher $quotationHasher
    ) {
    }

    /**
     * Accept a quotation and update its status and type
     *
     * @param Quotation $quotation The ID of the quotation to accept
     * @param Request $request
     *
     * @return View
     * @throws Throwable
     */
    public function accept(
        Quotation $quotation,
        Request $request
    ): View
    {
        $this->checkQuotationAvailability($quotation, $request);

        $quotation->updateOrFail(['type' => true, 'st' => Status::NEW, 'created_from' => OrderOrigin::FromQuotation]);

        $this->dispatcher->dispatch(
            new QuotationAcceptedEvent($quotation, $this->environment->tenant())
        );

        return view('tenant.quotations.accept_quotation', [
            'supplier_data' => tenantCustomFields()->toArray(),
            'quotation' => (new CalculationAction($quotation->load([
                'orderedBy',
                'orderedBy.profile',
                'items',
                'items.media',
                'items.services',
                'items.addresses',
                'items.children',
                'items.children.addresses',
                'services',
                'delivery_address',
                'invoice_address',
                'delivery_address.country', 'invoice_address.country',
            ])))->Calculate(),
            'logo' => tenantLogoUrl()
        ]);
    }

    /**
     * Reject a quotation and update its status
     *
     * @param Quotation $quotation The ID of the quotation to reject
     * @param Request $request
     *
     * @return View
     * @throws Throwable
     */
    public function reject(
        Quotation $quotation,
        Request $request
    ): View
    {
        $this->checkQuotationAvailability($quotation, $request);

        $quotation->updateOrFail(['st' => Status::REJECTED]);

        $this->dispatcher->dispatch(
            new QuotationRejectedEvent($quotation, $this->environment->tenant())
        );

        return view('tenant.quotations.reject_quotation', [
            'supplier_data' => tenantCustomFields()->toArray(),
            'quotation' => (new CalculationAction($quotation->load([
                'orderedBy',
                'orderedBy.profile',
                'items',
                'items.media',
                'items.services',
                'items.addresses',
                'items.children',
                'items.children.addresses',
                'services',
                'delivery_address',
                'invoice_address',
                'delivery_address.country', 'invoice_address.country',
            ])))->Calculate(),
            'logo' => tenantLogoUrl()
        ]);
    }

    /**
     * Check the availability of a quotation
     *
     * @param Quotation $quotation The quotation to check
     * @param Request $request The request object
     *
     * @throws HttpException If the quotation is no longer available or out of date
     */
    private function checkQuotationAvailability(
        Quotation $quotation,
        Request $request
    ): void
    {
        # Ensure that the quotation still in the applicable status
        if ($quotation->getAttribute('st') !== Status::WAITING_FOR_RESPONSE->value) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Quotation is not in the applicable status');
        }

        # Ensure that quotation data has not been changed after sending the invitation link
        if (false === $this->quotationHasher->verify($quotation, $request->query('qh'))) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Quotation is no longer available');
        }
    }
}

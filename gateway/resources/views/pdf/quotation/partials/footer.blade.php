@include('pdf.quotation.partials.totals')

@if($type == 'quotations')
    <p class="footer-info">
        {{ __('quotations.payment_terms', ['payment_terms' => $payment_terms, 'invoice_number' => $invoice_number]) }}.
    </p>

@endif

<div class="page-number"></div>

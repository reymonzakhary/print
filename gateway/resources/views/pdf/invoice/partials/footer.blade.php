@include('pdf.invoice.partials.totals')

<p class="footer-info">
    {{ __('invoices.payment_terms', ['payment_terms' => $payment_terms, 'invoice_number' => $invoice_number]) }}.
</p>

<div class="page-number"></div>

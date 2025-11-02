<div>
    <table class="invoice-details" style="float: left; width: 60%;">
        <tr>
            <td style="width: 40%">{{ __('invoices.invoice_number') }}:</td>
            <td style="width: 60%">{{ $invoice_number }}</td>
        </tr>
        <tr>
            <td style="width: 40%">{{ __('invoices.creation_date') }}:</td>
            <td style="width: 60%">{{ $invoice_date }}</td>
        </tr>
        <tr>
            <td style="width: 40%">{{ __('invoices.due_date') }}:</td>
            <td style="width: 60%">{{ $invoice_due_date }}</td>
        </tr>
    </table>
    <div style="float: right; width: 40%;">
        <span class="invoice-title">{{ __('invoices.invoice') }}</span>
    </div>
    <div style="clear: both;"></div>
</div>

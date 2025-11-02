<div class="items-container {{ $is_extra_page ? 'extra' : '' }}">
    <table class="items">
        <thead>
        <tr>
            <th style="width: 35%">{{ __('invoices.item') }}</th>
            <th style="width: 12%; text-align: center;">{{ __('invoices.vat') }}</th>
            <th style="width: 20%; text-align: center">{{ __('invoices.amount') }}</th>
            <th style="width: 10%; text-align: center">{{ __('invoices.quantity') }}</th>
            <th style="width: 14%; text-align: center">{{ __('invoices.shipping') }}</th>
            <th style="width: 19%; text-align: center">{{ __('invoices.sub_total') }}</th>
        </tr>
        </thead>
        <tbody>
        {{-- Met 16px font-size passen er 7 op 1 pagina --}}
        @each('pdf.invoice.partials.table.item', $items, 'item_data')
        </tbody>
    </table>
</div>

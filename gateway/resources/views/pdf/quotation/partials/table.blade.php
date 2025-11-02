<div class="items-container {{ $is_extra_page ? 'extra' : '' }}">
    <table class="items">
        <thead>
        <tr>
            <th style="width: 7%; text-align: left">{{ __('quotations.quantity') }}</th>
            <th style="width: 50%">{{ __('quotations.item') }}</th>
            <th style="width: 13%; text-align: center">{{ __('quotations.amount') }}</th>
            <th style="width: 10%; text-align: center">{{ __('quotations.shipping') }}</th>
            <th style="width: 11%; text-align: center">{{ __('quotations.sub_total') }}</th>
            <th style="width: 8%; text-align: center;">{{ __('quotations.vat') }}</th>
        </tr>
        </thead>
        <tbody>
        {{-- Met 16px font-size passen er 7 op 1 pagina --}}
        @each('pdf.quotation.partials.table.item', $items, 'item_data')
        </tbody>
    </table>
</div>

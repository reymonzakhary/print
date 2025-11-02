<table class="totals">
    <tr>
        <td style="width: 81%; text-align: right; ">{{ __('quotations.shipping_cost') }}</td>
        <td class="currency-cell" style="width: 8%; color: #5A6572">
            {{ $shipping_cost->format() }}
        </td>
        <td class="currency-cell" style="width: 8%;">
        </td>
    </tr>

    <tr>
        <td style="width: 81%; text-align: right; ">{{ __('quotations.total_excl_vat') }}</td>
        <td class="currency-cell">

        </td>
        <td class="currency-cell" style="width: 8%;">{{ $total_excl_vat->format() }}</td>
    </tr>

    @foreach ($vat_amounts as $vat)
        <tr>
            <td style="width: 81%; text-align: right; "> {{ __('quotations.vat') }}
                {{ $vat['vat_percentage'] }}%
                <strong>({{ moneys()->setDecimal(0)->setAmount($vat['subtotal'])->format()}})</strong>
            </td>
            <td class="currency-cell" style="width: 12%; color: #5A6572">
                {{ moneys()->setAmount($vat['total_vat'])->format()}}
            </td>
            <td class="currency-cell" style="width: 8%;">
            </td>
        </tr>
    @endforeach

    <tr>
        <td style="width: 81%; text-align: right; ">{{ __('quotations.total_vat') }} </td>
        <td class="currency-cell">

        </td>
        <td class="currency-cell" style="width: 8%;">{{ moneys()->setAmount($vat_total)->format() }}</td>

    </tr>

    <tr>
        <td style="width: 81%; text-align: right; "><strong>{{ __('quotations.to_pay') }}</strong>
        </td>
        <td class="currency-cell">

        </td>
        <td class="currency-cell" style="width: 8%;"><strong>{{ $total_incl_vat->format() }}</strong>
        </td>
    </tr>
</table>

<table class="totals">

    @php
        use App\Plugins\Moneys;
        $subtotal = 0;
        $total_shipping_cost = 0;
        $vat_total = 0;
        foreach ($products as $product) {
            $total_shipping_cost += $product['shipping_cost'];
            $subtotal += $product['subtotal'];
            $vat_total += $product['vat'] * $product['subtotal_incl_shipping_cost'] / 100;
        }
        $total_shipping_cost = (new Moneys())->setAmount($total_shipping_cost);
        $subtotal = (new Moneys())->setAmount($subtotal);
    @endphp

    <tr>
        <td style="width: 81%; text-align: right; ">{{ __('invoices.shipping_cost') }}</td>
        <td class="currency-cell" style="width: 8%; text-align: center; color: #5A6572">
            {{ $total_shipping_cost->format() }}
        </td>
        <td class="currency-cell" style="width: 8%;">
        </td>
    </tr>


    <tr>
        <td style="width: 81%; text-align: right; padding-right: 1rem;">{{ __('invoices.total_excl_vat') }}</td>
        <td class="currency-cell" style="width: 19%; text-align: right;">{{ $total_excl_vat->format() }}</td>
    </tr>
    
    @php
        $vats = collect($products)->groupBy('vat')->map(function ($group, $vat) {
                            return [
                                'vat' => $vat,
                                'subtotal_incl_shipping_cost' => $group->sum('subtotal_incl_shipping_cost'),
                            ];
                        })->values();
    @endphp
    @foreach ($vats as $vat)
        <tr>
            <td style="width: 81%; text-align: right; "> {{ __('quotations.vat') }}
                {{ $vat['vat'] }}%
                <strong>({{ moneys()->setDecimal(0)->setAmount($vat['subtotal_incl_shipping_cost'] / 100)->format()}})</strong>
            </td>
            <td class="currency-cell" style="width: 8%; color: #5A6572; text-align: center">
                {{ moneys()->setAmount($vat['vat'] * $vat['subtotal_incl_shipping_cost'] / 100)->format()}}
            </td>
            <td class="currency-cell" style="width: 8%;">
            </td>
        </tr>
    @endforeach

    <tr>
        <td style="width: 81%; text-align: right;">{{ __('invoices.total_vat') }} </td>

        <td class="currency-cell" style="width: 8%; text-align: right;">{{ moneys()->setAmount($vat_total)->format() }}</td>

    </tr>




    <tr>
        <td style="width: 81%; text-align: right; padding-right: 1rem;"><strong>{{ __('invoices.grand_total') }}</strong>
        </td>
        <td class="currency-cell" style="width: 19%; text-align: right;"><strong>{{ $total_incl_vat->format() }}</strong>
        </td>
    </tr>
</table>

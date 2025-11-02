@php
    use App\Plugins\Moneys;

    $unit_price = (new Moneys())
        ->setPrecision(5)
        ->setAmount($item_data['unit_price']);

    $sub_total = (new Moneys())
        ->setAmount($item_data['subtotal_incl_shipping_cost']);

    $shipping_cost = (new Moneys())
        ->setAmount($item_data['shipping_cost']);

@endphp

<tr>
    <td style="width: 35%">
        {{ $item_data['name'] }} <br>

        @if (isset($item_data['description']))
            <span style="font-size: 0.75rem">{{ $item_data['description'] }}</span>
    @endif
    </td>

    <td style="width: 12%; text-align: center;">{{ $item_data['vat'] }} <span style="margin-right: 2px">%</span>
    </td>

    <td style="width: 20%; text-align: center">{{ $unit_price->format() }}</td>

    <td style="width: 10% text-align: center">{{ $item_data['quantity'] }}</td>

    <td style="width: 14% text-align: center">{{ $shipping_cost->format() }}</td>

    <td style="width: 19%; text-align: center">
        {{ $sub_total->format() }}
    </td>
</tr>

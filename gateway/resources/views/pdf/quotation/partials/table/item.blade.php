<tr>
    <td style="width: 7%; text-align: left">{{ $item_data['quantity'] }} </td>

    <td style="width: 50%">
        {{ $item_data['name'] }} <br>
        <p style="font-size: 0.75rem; margin-top: 0.25rem">{{ $item_data['description'] }}</p>
    </td>

    <td style="width: 13%; text-align: center">
            {{ $item_data['ppp'] }}
    </td>
    <td style="width: 10%; text-align: center">
        {{ $item_data['shipping_cost'] }}
    </td>
    <td style="width: 12%; text-align: center">
        {{ $item_data['subtotal'] }}
    </td>
    <td style="width: 8%; text-align: center;">
        {{ (float)$item_data['vat'] }} <span style="margin-right: 2px">%</span>
    </td>
</tr>


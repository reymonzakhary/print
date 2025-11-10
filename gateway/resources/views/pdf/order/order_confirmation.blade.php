@php
    use App\Actions\PriceAction\CalculationAction;
    $order = (new CalculationAction($order->load([
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
    ])))->Calculate();
    $customer_data = $order->orderedBy;
//    $supplier_data = $supplierData;

    $products = $order->items()->whereStatusIsNotCancelled()->get()->map(function ($item) {
        $item['type'] = 'product';
        $item['name'] = $item->product->category['name'];
        $item['description'] = collect($item->product->items??[])
        ->map(fn($item) => ucwords(str_replace('-', ' ', $item['key'])) . ': ' . ucwords(str_replace('-', ' ', $item['value'])))
        ->implode(', ');
        $item['vat'] = $item->product->price['vat'];
        $item['ppp'] = moneys()->setPrecision(5)->setAmount($item->product->price['ppp'])->format();
        $item['quantity'] = $item->pivot->getAttribute('qty');
        $item['shipping_cost'] = moneys()->setAmount($item->pivot->getAttribute('shipping_cost'));
        $item['selling_price_ex'] = $item->product->price['selling_price_ex'];
        $item['subtotal'] = moneys()->setAmount($item->product->price['selling_price_ex']);
        $item['total'] = moneys()->setAmount($item->product->price['selling_price_inc']);
        return $item;
    })->merge($order->services()->get()->map(function ($service) {
        $service['type'] = 'service';
        $service['name'] = $service->name;
        $service['description'] = $service->description;
        $service['vat'] = $service->pivot->getAttribute('vat');
        $service['ppp'] = $service->price->setPrecision(5)->format();
        $service['quantity'] = $service->pivot->getAttribute('qty');
        $service['shipping_cost'] = \moneys()->setAmount(0)->format();
        $service['selling_price_ex'] = $service->price->multiply($service->pivot->getAttribute('qty'))->amount();
        $service['subtotal'] = $service->price->multiply($service->pivot->getAttribute('qty'));
        $service['total'] = $service->price->setTax($service->pivot->getAttribute('vat'))->multiply($service->pivot->getAttribute('qty'));
        return $service;
    }));

    /*
    /*
     * Company Data
     */
    $customer_address = $customer_data?->invoiceAddress();
    $company_name = "";
    $company_representative = $customer_data->profile?->first_name . ' ' . $customer_data->profile?->last_name;
    $company_address = trim($customer_address?->getAttribute('address') . ' ' . $customer_address?->getAttribute('number'));
    $company_zipcode = $customer_address?->getAttribute('zip_code');
    $company_city = $customer_address?->getAttribute('city');

    /*
     * Invoice Data
     */
    $invoice_number = $order->getAttribute('id');
    $invoice_date = $order->getAttribute('created_at');
    $invoice_due_date = $order->getAttribute('expire_at');
    if (!($invoice_due_date instanceof \Carbon\Carbon)) {
        $invoice_due_date = \Carbon\Carbon::parse($invoice_due_date);
    }

    /*
     * Payment Terms
     */
    $invoice_date_obj = \Carbon\Carbon::parse($invoice_date);
    $invoice_due_date_obj = \Carbon\Carbon::parse($invoice_due_date);
    $payment_terms_days = $invoice_due_date_obj->diffInDays($invoice_date_obj);
    $payment_terms = $payment_terms_days;

    /**
     * Vats
    */
    $all_items = collect($order->items_price_array)
        ->map(function ($item) use ($products) {
            $product = $products->firstWhere('id', $item['item_id']);
            return [
                'vat_percentage' => (int) $item['vat']['vat_percentage'],
                'vat' => $item['vat']['vat'],
                'subtotal' => $product->subtotal->add($item['shipping_cost']/100)->amount(),
            ];
        });

    $all_services = collect($order->order_services_price_array)
        ->map(function ($service) use ($products) {
            $product = $products->firstWhere('id', $service['service_id']);
            return [
                'vat_percentage' => (int) $service['vat']['vat_percentage'],
                'vat' => $service['vat']['vat'],
                'subtotal' => $product->subtotal->amount(),
            ];
        });

    $combined = $all_items->merge($all_services);

    $vat_amounts = $combined
        ->groupBy('vat_percentage')
        ->map(function ($group, $percentage) {

            $subtotal_sum = $group->sum('subtotal');
            $vat_sum = $group->sum('vat');
            return [
                'subtotal' => $subtotal_sum,
                'vat_percentage' => $percentage,
                'total_vat' => $vat_sum * 100,
            ];
        })
        ->values();

    $vat_total = $order->vats_price;


    /*
     * Vat Amounts
     */
    $shipping_cost = moneys()->setAmount($order->getAttribute('shipping_cost'));
    $total_incl_vat = moneys()->setAmount($order->getAttribute('total_price'));

    $total_excl_vat = moneys()->setAmount($order->getAttribute('subTotal_price'));
    /*
     * Appereance Settings
     */
    $currency_symbol = $total_excl_vat->getCurrency();
    $letterhead_size = 'A4';
    /**
      * Convert products to Array for iterative operations
      */
    $products = $products->toArray();
    /*
     * The product row limit is calculated by dividing the rowHeight (2.5 * fontSize) by the height of the first page table.
     */

    $delivery_address = $order->delivery_address()->first();

@endphp

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation #{{ $order->order_nr }}</title>
    <style>

        body {
            font-size: 9px;
            font-family: "Helvetica", serif;
        }

        .header, .footer {
            text-align: center;
            font-size: 12px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
        }

        .section-title {
            font-weight: bold;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .items th {
            border-bottom: 1px solid #000;
            text-align: left;
            padding: 0.5rem 1rem;
        }

        .items th:first-child {
            padding-left: 0;
        }

        .items th:last-child {
            padding-right: 0;
        }

        .items td {
            vertical-align: top;
            padding: 0.5rem 1rem;
            height: 2.5rem;
            overflow: hidden;
        }

        .items td:first-child {
            padding-left: 0;
        }

        .items td:last-child {
            padding-right: 0;
        }

        .items tr {
            border-bottom: 1px solid #aaa;
        }

        .items tr:last-child {
            border-bottom: none;
        }

        .totals tr:first-child {
            border-top: 1px solid #000;
        }

        .totals tr:first-child td {
            padding-top: 0.5rem;
        }

        .totals td {
            padding: 0.25rem 0;
        }
        .address {
            text-align: left;
            margin-bottom: 5px;
        }

    </style>
</head>
<body>

{{--@if (!empty($logo))--}}
{{--    <div class="logo-container">--}}
{{--        <img src="{{ $logo }}" class="logo"  alt=""/>--}}
{{--    </div>--}}
{{--@endif--}}



<div class="header">
    <div class="title">Order Confirmation</div>
    <div>Order Number: <strong>#{{ $order->order_nr }}</strong></div>
    <div>Order Date: {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</div>
</div>


<div class="address">
    <div>{{ $delivery_address->address }}</div>
    <div>{{ $delivery_address->number }}</div>
    <div>{{ $delivery_address->zip_code }} {{ $delivery_address->city }}</div>
</div>


<hr>
<div class="section-title">Order Items</div>
<div class="items-container">
    <table class="items">
    <thead>
    <tr>
        <th>Quantity</th>
        <th>Product/Service</th>
        <th>Unit Price</th>
        <th>Shipping</th>
        <th>Subtotal</th>
        <th>VAT %</th>
    </tr>
    </thead>
    <tbody>

    @foreach($products as $index => $item)
        <tr>
            <td style="width: 7%; text-align: left">{{ $item['quantity'] }} </td>
            <td style="width: 50%">
                {{ $item['name'] }} <br>
                <p style="font-size: 0.75rem; margin-top: 0.25rem">{{ $item['description'] }}</p>
            </td>
            <td style="width: 13%; text-align: center">{{ $item['ppp'] }}</td>
            <td style="width: 10%; text-align: center">{{ $item['shipping_cost'] }}</td>
            <td style="width: 12%; text-align: center">{{ $item['subtotal'] }}</td>
            <td style="width: 8%; text-align: center;">{{ (float)$item['vat'] }} <span style="margin-right: 2px">%</span></td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>


<div class="section-title">Totals</div>
<table class="total-table">
    <tr>
        <td class="right" style="width: 85%;">Subtotal (excl. VAT):</td>
        <td class="right">{{ $total_excl_vat->format() }}</td>
    </tr>
    <tr>
        <td class="right">VAT:</td>
        <td class="right">{{ moneys()->setAmount($vat_total)->format() }}</td>
    </tr>
    <tr>
        <td class="right"><strong>Total (incl. VAT):</strong></td>
        <td class="right"><strong>{{ $total_incl_vat->format() }}</strong></td>
    </tr>
</table>

<hr>

<div class="footer">
    <p>Thank you for your order!</p>
</div>

</body>
</html>

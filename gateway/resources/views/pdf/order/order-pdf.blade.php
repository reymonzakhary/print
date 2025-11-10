@php



$customer_data = $order->orderedBy;
if (!$customer_data) {
    $customer_data = $order->customer ? json_decode(json_encode($order->customer)) : null;
}
$supplier_data = $supplierData;
$type = 'orders';

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

$customer_address = $order->invoice_address()->first() ?? null;
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
$direction = $settings['customer_address_position_direction'];
$offset = is_numeric($settings['customer_address_position']) ? (int) $settings['customer_address_position'] : 0;
$font_size = is_numeric($settings['font_size']) ? (int) $settings['font_size'] : 12;
$font_family = $settings['font'];
$logo = $settings['logo'];
$logo_position = $settings['logo_position'];
$background = $settings['background'];
$logo_width = $settings['logo_width'];

/**
  * Convert products to Array for iterative operations
  */
$products = $products->toArray();
/*
 * The product row limit is calculated by dividing the rowHeight (2.5 * fontSize) by the height of the first page table.
 */
$amount_of_items = count($products);
$first_table_height = 467 + 25 * (16 - $font_size) - $offset;
$extra_table_height = 518;
$first_product_row_limit = floor($first_table_height / (2.5 * $font_size)) - 3;
$extra_product_row_limit = floor($extra_table_height / (2.5 * $font_size)) - 2;
$row_height = 1.5 * $font_size;

@endphp

@extends('pdf.quotation.layouts.main')

@section('content')
    @include('pdf.quotation.partials.header')

    @include('pdf.quotation.partials.details')

    @if ($amount_of_items <= $first_product_row_limit)
        @include('pdf.quotation.partials.table', ['items' => array_slice($products, 0, $first_product_row_limit), 'is_extra_page' => false])
        @include('pdf.quotation.partials.footer')
    @else
        @include('pdf.quotation.partials.table', ['items' => array_slice($products, 0, $first_product_row_limit), 'is_extra_page' => false])
        @for ($i = 7; $i < $amount_of_items; $i += $extra_product_row_limit)
            <table class="totals">
                <tr>
                    <td style="width: 81%; text-align: right; padding-right: 1rem;">{{ __('quotations.sub_total_excl_vat') }}</td>
                    <td class="currency-cell" style="width: 19%;">
                        <span class="currency-symbol">{{ $currency_symbol }}</span>
                        {{ number_format(collect(array_slice($products, 0, $i))->sum(function($product) { return $product['subtotal']->getAmount(); }), 2, ',', '.') }}
                    </td>
                </tr>
            </table>
            <p class="footer-info">{{ __('quotations.next_page_message') }}</p>
            <div class="page_break"></div>
            @include('pdf.quotation.partials.header')
            @include('pdf.quotation.partials.table', ['items' => array_slice($products, $i, $extra_product_row_limit), 'is_extra_page' => true])
        @endfor
        @include('pdf.quotation.partials.footer')
    @endif
@endsection

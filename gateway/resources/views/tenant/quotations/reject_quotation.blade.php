@php
    use App\Actions\PriceAction\CalculationAction;
    use App\Facades\Settings;
    use App\Models\Tenants\Item;
    use App\Models\Tenants\Service;
    use App\Plugins\Money;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('quotations.reject.title') }}</title>

    <style>
        :root {
            --primary-blue: #00A7E1;
            --primary-light: #E5F6FC;
            --success-primary: #059669;
            --success-light: #ECFDF5;
            --success-dark: #065F46;
            --error-red: #DC2626;
            --error-light: #FEE2E2;
            --error-dark: #991B1B;
            --gray-50: #FAFBFC;
            --gray-100: #F5F7F9;
            --gray-200: #E8EBED;
            --gray-300: #D8DDE1;
            --gray-600: #5A6572;
            --gray-800: #2C3641;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--gray-50);
            color: var(--gray-800);
            line-height: 1.5;
            margin: 0;
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .logo-header {
            text-align: left;
            margin-bottom: 2rem;
        }

        .logo-header img {
            max-width: 180px;
            height: auto;
        }

        .rejection-card {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
            padding: 2rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--error-light);
        }

        .x-circle {
            width: 72px;
            height: 72px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            background: var(--error-light);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.4s ease-out;
            border: 2px solid var(--error-red);
        }

        .x-mark {
            width: 36px;
            height: 36px;
            color: var(--error-red);
        }

        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .header h1 {
            color: var(--error-dark);
            font-size: 2rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .header p {
            color: var(--gray-600);
            margin: 0;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .quotation-number {
            display: inline-block;
            background: var(--error-light);
            color: var(--error-red);
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            margin-top: 1.25rem;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin: 2.5rem 0;
        }

        .info-card {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 16px;
            padding: 1.5rem;
            transition: transform 0.2s ease;
        }

        .info-card:hover {
            transform: translateY(-2px);
        }

        .info-card h3 {
            font-size: 1rem;
            text-transform: uppercase;
            color: var(--primary-blue);
            margin: 0 0 1.25rem;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--gray-200);
            padding-bottom: 0.75rem;
        }

        .info-card p {
            margin: 0;
            color: var(--gray-800);
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .items-list {
            background: var(--gray-50);
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 2rem;
            border: 1px solid var(--gray-200);
        }

        .items-header {
            display: grid;
            grid-template-columns: 70px 70px 190px 90px 90px 70px 40px;
            gap: 1.5rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-weight: 500;
            color: var(--primary-blue);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
            /* text-align: center; */
            box-shadow: var(--shadow-sm);
        }

        .item-row {
            display: grid;
            grid-template-columns: 70px 70px 190px 90px 90px 70px 40px;
            gap: 1.5rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--gray-200);
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .item-row:hover {
            background: white;
            border-radius: 12px;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-name-wrapper {
            position: relative;
            display: inline-block;
        }

        .item-name {
            font-weight: 500;
            color: var(--gray-800);
            font-size: 0.9rem;
            text-align: left;
            line-height: 1.4;

            /* Text truncation */
            white-space: normal;
            word-wrap: break-word;
            word-break: break-word;

            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;

            max-width: 100%;
            cursor: help;
        }

        /* Custom tooltip styling */
        .item-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-bottom: 8px;
            padding: 8px 12px;
            background-color: rgba(0, 0, 0, 0.9);
            color: white;
            font-size: 0.875rem;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s, visibility 0.2s;
            pointer-events: none;
            z-index: 1000;
            max-width: 300px;
            white-space: normal;
            text-align: center;
        }

        /* Tooltip arrow */
        .item-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: rgba(0, 0, 0, 0.9);
        }

        /* Show tooltip on hover */
        .item-name-wrapper:hover .item-tooltip {
            opacity: 1;
            visibility: visible;
        }

        /* Optional: Adjust tooltip position if it goes off-screen */
        .item-row:first-child .item-tooltip {
            bottom: auto;
            top: 100%;
            margin-bottom: 0;
            margin-top: 8px;
        }

        .item-row:first-child .item-tooltip::after {
            top: auto;
            bottom: 100%;
            border-top-color: transparent;
            border-bottom-color: rgba(0, 0, 0, 0.9);
        }

        .col-name {
            text-align: left;
        }
        .col-vat {
            text-align: right;
        }

        .item-row .item-description{
            font-weight: 500;
            color: var(--gray-800);
            font-size: 0.7rem;
            overflow: hidden;
        }

        .item-quantity {
            color: var(--gray-600);
            text-align: left;
            font-family: var(--font-mono);
            font-size: 0.9rem;
        }

        .item-price {
            text-align: center;
            white-space: nowrap;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            position: relative;
        }

        .item-vat {
            text-align: right;
            white-space: nowrap;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            position: relative;
        }

        .currency-symbol {
            position: absolute;
            left: 0;
            color: var(--gray-600);
            font-weight: normal;
            width: 1.5ch;
            text-align: left;
        }

        .total-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 16px;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
        }

        .total-row {
            display: flex;
            justify-content: flex-end;
            gap: 2rem;
            margin-bottom: 1rem;
            color: var(--gray-600);
            font-size: 1.05rem;
            align-items: center;
        }

        .total-row .amount {
            min-width: 140px;
            text-align: right;
            font-family: var(--font-mono);
            font-size: 1rem;
            position: relative;
            padding-left: 1.5ch;
        }

        .total-amount {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary-blue);
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 2px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 2rem;
        }

        .total-amount .amount {
            min-width: 140px;
            font-family: var(--font-mono);
            text-align: right;
            position: relative;
            padding-left: 1.5ch;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            body {
                padding: 1rem;
            }

            .rejection-card {
                padding: 1.25rem;
            }

            .items-header, .item-row {
                grid-template-columns: 1fr 80px 80px 120px;
                padding: 0.75rem;
            }

            .total-row, .total-amount {
                gap: 1rem;
            }

            .total-row .amount, .total-amount .amount {
                min-width: 120px;
                position: relative;
                padding-left: 1.5ch;
            }
        }
    </style>
</head>
<body>
    @php

        $customer = $quotation->orderedBy;
        $customerDefaultAddress = $customer?->invoiceAddress();

        $products = $quotation->items()->whereStatusIsNotCancelled()->get()->map(function ($item) {
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
        })->merge($quotation->services()->get()->map(function ($service) {
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
$customer_address = $customer?->invoiceAddress();
$company_name = "";
$company_representative = $customer->profile?->first_name . ' ' . $customer->profile?->last_name;
$company_address = trim($customer_address?->getAttribute('address') . ' ' . $customer_address?->getAttribute('number'));
$company_zipcode = $customer_address?->getAttribute('zip_code');
$company_city = $customer_address?->getAttribute('city');

/*
 * Invoice Data
 */
$invoice_number = $quotation->getAttribute('id');
$invoice_date = $quotation->getAttribute('created_at');
$invoice_due_date = $quotation->getAttribute('expire_at');
if (!($invoice_due_date instanceof \Carbon\Carbon)) {
    $invoice_due_date = \Carbon\Carbon::parse($invoice_due_date);
}

/*
 * Payment Terms
 */
$invoice_date_obj = \Carbon\Carbon::parse($invoice_date);
$invoice_due_date_obj = \Carbon\Carbon::parse($invoice_due_date);
$payment_terms_days = $invoice_due_date_obj->diffInDays($invoice_date_obj);
$payment_terms = "{$payment_terms_days} dagen";

/**
 * Vats
*/
$all_items = collect($quotation->items_price_array)
    ->map(function ($item) use ($products) {
        $product = $products->firstWhere('id', $item['item_id']);
        return [
            'vat_percentage' => (int) $item['vat']['vat_percentage'],
            'vat' => $item['vat']['vat'],
            'subtotal' => $product->subtotal->add($item['shipping_cost']/100)->amount(),
        ];
    });

$all_services = collect($quotation->order_services_price_array)
    ->map(function ($service) use ($products) {
        $product = $products->firstWhere('id', $service['service_id']);
        return [
            'vat_percentage' => (int) $service['vat']['vat_percentage'],
            'vat' => $service['vat']['vat'],
            'subtotal' => $product->subtotal->amount(),//
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

$vat_total = $quotation->vats_price;


/*
 * Vat Amounts
 */
$shipping_cost = moneys()->setAmount($quotation->getAttribute('shipping_cost'));
$total_incl_vat = moneys()->setAmount($quotation->getAttribute('total_price'));

$total_excl_vat = moneys()->setAmount($quotation->getAttribute('subTotal_price'));
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
$amount_of_items = count($products);

$contextAddress = $quotation->context->addresses()->first();
    @endphp

    <div class="container">
        <div class="logo-header">
            <img src="{{ $logo }}" alt="Company Logo">
        </div>

        <div class="rejection-card">
            <div class="x-circle">
                <svg class="x-mark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>

            <div class="header">
                <h1>{{ __('quotations.reject.title') }}</h1>
                <p>{{ __('quotations.reject.description') }}</p>
                <div class="quotation-number">{{ __('quotations.quotation_number') }}: #{{ $quotation->getAttribute('id') }}</div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h3>{{ __('From') }}</h3>
                    <p>
                        <strong>{{ $supplier_data['company_name'] }}</strong><br>
                        @if($contextAddress)
                            {{ $contextAddress->getAttribute('number') }}<br>
                            {{ $contextAddress->getAttribute('zip_code') }}, {{ $contextAddress->getAttribute('city') }}<br>
                            {{ $contextAddress->pivot->getAttribute('phone_number') }}<br>
                        @endif
                        {{ $supplier_data['email'] }}
                    </p>
                </div>

                <div class="info-card">
                    <h3>{{ __('To') }}</h3>
                    <p>
                        @if($customer)
                            <strong>{{ $customer->profile?->first_name }} {{ $customer->profile?->last_name }}</strong><br>
                            {{ $customer->email }}<br>
                            @if($customerDefaultAddress)
                                {{ $customerDefaultAddress->getAttribute('address') }} {{ $customerDefaultAddress->getAttribute('number') }}<br>
                                {{ $customerDefaultAddress->getAttribute('zip_code') }}, {{ $customerDefaultAddress->getAttribute('city') }}
                            @endif
                        @endif
                    </p>
                </div>
            </div>

            <div class="items-list">
                <div class="items-header">
                    <div class="col-quantity">{{ __('quotations.quantity') }}</div>
                    <div class="col-name">{{ __('Name') }}</div>
                    <div class="col-description">{{ __('quotations.description') }}</div>
                    <div class="col-vat">{{ __('quotations.amount') }}</div>
                    <div class="col-shipping">{{ __('quotations.shipping') }}</div>
                    <div class="col-subtotal">{{ __('quotations.sub_total') }}</div>
                    <div class="col-vat">{{ __('quotations.vat') }}</div>
                </div>

                @foreach ($products as $item)

                    <div class="item-row">
                        <div class="item-quantity">{{ $item['quantity'] }}</div>
                        <div class="item-name-wrapper">
                            <div class="item-name">
                                {{ $item['name'] }}
                            </div>
                            <div class="item-tooltip">
                                {{ $item['name'] }}
                            </div>
                        </div>
                        <div class="item-description">{{ $item['description'] }}</div>
                        <div class="item-price">{{ $item['ppp'] }}</div>
                        <div class="item-price">{{ $item['shipping_cost'] }}</div>
                        <div class="item-price">{{ $item['subtotal']->format() }}</div>
                        <div class="item-vat">{{ (float)$item['vat'] }}<span style="margin-right: 2px">%</span></div>
                    </div>
                @endforeach
            </div>

            <div class="total-section">
                <div class="total-row" >
                    <span >{{ __('quotations.shipping') }}:</span>
                    <div class="amount" style="text-align: start;">
                        <span>{{ $shipping_cost->format() }}</span>
                    </div>
                </div>

                <div class="total-row">
                    <span>{{ __('quotations.subtotal') }}:</span>
                    <div class="amount">

                        <span>{{ $total_excl_vat->format() }}</span>
                    </div>
                </div>

                @foreach ($vat_amounts as $vat)
                    <div class="total-row">
                        <span>{{ __('quotations.vat') }} {{ $vat['vat_percentage'] }}%
                            <strong>({{ moneys()->setDecimal(0)->setAmount($vat['subtotal'])->format()}})</strong> :</span>
                        <div class="amount" style="margin-right: -18px">
                            <span style="margin-right: 100px; font-style: italic; color: #5A6572; font-size: 14px"> {{ moneys()->setAmount($vat['total_vat'])->format()}}</span>
                        </div>
                    </div>
                @endforeach
                <div class="total-row">
                    <span>{{ __('quotations.total_vat') }}</span>
                    <div class="amount">
                        <span> {{moneys()->setAmount($vat_total)->format()}}</span>
                    </div>
                </div>

                <div class="total-amount">
                    <span>{{ __('quotations.grand_total') }}:</span>
                    <div class="amount">
                        <span>{{ $total_incl_vat->format() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

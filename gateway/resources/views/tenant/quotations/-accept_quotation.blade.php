@php
    /**
     * This template is based on the Simple HTML Invoice Template by SparkSuite.
     * Source: https://github.com/sparksuite/simple-html-invoice-template
     */

        use App\Actions\PriceAction\CalculationAction;
        use App\Facades\Settings;
        use App\Models\Tenant\Item;
        use App\Models\Tenant\Service;
        use App\Plugins\Money;
@endphp

    <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <title>{{ __('quotations.accept.title') }}</title>


    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            color: #555;
        }

        .invoice-table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-table td {
            padding: 5px;
        }

        .invoice-table tr td:last-child {
            text-align: right;
        }

        .invoice-table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-table tr.information table {
            padding-bottom: 20px;
        }

        .invoice-table td .quotation-information td {
            padding: 0;
        }

        .invoice-table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-table tr.item.last td {
            border-bottom: none;
        }

        .invoice-table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .invoice-box.rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .invoice-box.rtl .invoice-table {
            text-align: right;
        }

        .invoice-box.rtl .invoice-table tr td:nth-child(2) {
            text-align: left;
        }

        /* Alert Box Styles */
        .alert {
            max-width: 800px;
            background-color: #DEF7EC;
            border-left: 4px solid #0E9F6E;
            border-radius: 6px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            color: #03543F;
            margin-bottom: 1rem;
            padding: 1rem 1.5rem;
            position: relative;
            margin-left: auto;
            margin-right: auto;
        }

        .alert h1 {
            color: #15803d;
            font-size: 1.25rem;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 0.5rem;
            margin-top: 0;
        }

        .alert p {
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 0;
        }

        /* Animation */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert {
            animation: slideIn 0.3s ease-out;
        }

        .pricing-grid {
            width: 100%;
        }

        .pricing-grid tr td {
            border-bottom: none !important;
            padding: 0px !important;
        }

        body > div.invoice-box > table > tbody > tr > td:first-child {
            width: 30%;
        }
    </style>
</head>
@php
    $vat = Settings::vat()->value
@endphp
<body>
<article class="alert">
    <h1>{{ __('quotations.accept.title') }}</h1>
    <p>
        {{ __('quotations.accept.description') }}
    </p>
</article>
<div class="invoice-box">
    <table cellpadding="0" cellspacing="0" class="invoice-table">
        <tr class="top">
            <td colspan="2">
                <table style="width: 100%;">
                    <tr>
                        <td style="text-align: left;">
                            <img
                                src="{{ $logo }}"
                                style="width: 100%; max-width: 300px; margin-bottom: 0.5rem;"
                            />
                            <br/>
                            <br/>
                            @php
                                $quotation = (new CalculationAction($quotation))->calculate();

                                $customer = $quotation->orderedBy;
                                $customerDefaultAddress = $customer?->invoiceAddress();

                                $contextAddress = $quotation->context->addresses()->first();
                            @endphp

                            @if($customer)
                                {{ $customer->profile?->first_name }} {{ $customer->profile?->last_name }}
                                <br/>
                            @endif

                            @if($customerDefaultAddress)
                                {{ $customerDefaultAddress->getAttribute('address') }} {{ $customerDefaultAddress->getAttribute('number') }}
                                <br/>

                                {{ sprintf('%s, %s', $customerDefaultAddress->getAttribute('zip_code'), $customerDefaultAddress->getAttribute('city')) }}
                            @endif

                        </td>
                        <td style="text-align: left;">
                            {{ $supplier_data['company_name'] }}<br/>

                            @if($contextAddress)
                                {{ $contextAddress->getAttribute('number') }} <br/>
                                {{ $contextAddress->getAttribute('zip_code') }}
                                , {{ $contextAddress?->getAttribute('city') }}<br/>
                                {{ $contextAddress->pivot->getAttribute('phone_number') }} <br/>
                            @endif

                            {{ $supplier_data['email'] }} <br/><br/>

                            @if(isset($supplier_data['coc']))
                                {{ __('COC') }}: {{ $supplier_data['coc'] }} <br/>
                                {{ $supplier_data['tax_nr'] }} <br/>
                            @endif

                            @if(isset($supplier_data['IBAN']))
                                <br/>
                                {{ $supplier_data['IBAN'] }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="information">
            <td colspan="2">
                <table class="quotation-information" style="width: 100%">
                    <tr>
                        <td style="text-align: left; width: 250px;">
                            <h1 class="title"> {{ __('quotations.quotation') }}</h1>
                            <table>
                                <tr>
                                    <td>{{ __('quotations.quotation_number') }}:</td>
                                    <td>{{ $quotation->getAttribute('id') ?? "-" }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('quotations.created') }}:</td>
                                    <td>{{ $quotation->created_at ? (new DateTime($quotation->created_at))->format('d-m-Y') : "-" }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('quotations.due') }}:</td>
                                    <td>{{ $quotation->expire_at ? (new DateTime($quotation->expire_at))->format('d-m-Y') : "-" }}</td>
                                </tr>
                            </table>
                        </td>

                        <td style="text-align: right;">
                            @if(count($quotation->address) > 0)
                                <br/>
                                <br/>
                                {{ $quotation->address[0]->pivot->company_name !== "No Company" ? $quotation->address[0]->pivot->company_name : "" }}
                                <br/>
                                {{ $quotation->orderedBy->profile->first_name }} {{ $quotation->orderedBy->profile->last_name }}
                                <br/>
                                {{ $quotation->orderedBy->email }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="heading">
            <td>{{ __('quotations.name') }}</td>
            <td style="width: 25%">{{ __('quotations.description') }}</td>
            <td style="width: 13%">{{ __('quotations.amount') }}</td>
            <td style="width: 12%; text-align: center;">{{ __('quotations.quantity') }}</td>
            <td style="width: 20%">{{ __('quotations.total') }}</td>
            <td style="width: 2%">{{ __('quotations.vat') }}</td>
            {{-- <td>
                <table class="pricing-grid">
                    <tr>
                        <td style="width: 33%;">{{ __('quotations.amount') }}</td>
                        <td style="width: 33%;">{{ __('quotations.quantity') }}</td>
                        <td>{{ __('quotations.total') }}</td>
                        <td>{{ __('quotations.vat') }}</td>
                    </tr>
                </table>
            </td> --}}
        </tr>

        @php
            $itemsAndServices = $quotation->items()->whereStatusIsNotCancelled()->get()->merge($quotation->services()->get());
        @endphp

        @foreach ($itemsAndServices as $itemOrService)
            @php
                $itemOrServiceName = match (true) {
                    $itemOrService instanceof Item => $itemOrService->product['category']['name'],
                    $itemOrService instanceof Service => $itemOrService->getAttribute('name')
                };

                $itemOrServiceDescription = match (true) {
                    $itemOrService instanceof Item => '',
                    $itemOrService instanceof Service => $itemOrService->getAttribute('description')
                };

                $itemOrServicePrice = match (true) {
                    $itemOrService instanceof Item => (new \App\Plugins\Moneys())->setAmount($itemOrService->product->price['gross_ppp']),
                    $itemOrService instanceof Service => $itemOrService->price
                };

                $itemOrServiceVatPercentage = match (true) {
                    $itemOrService instanceof Item => $itemOrService->product->price['vat'],
                    $itemOrService instanceof Service => $itemOrService->getAttribute('vat')
                };

                $itemOrServiceQuantity = $itemOrService->pivot->getAttribute('qty');

                $itemOrServicePriceTotalPrice = (new \App\Plugins\Moneys())->setAmount($itemOrServicePrice->amount() * $itemOrServiceQuantity);
            @endphp

            <tr class="item">
                <td>{{ $itemOrServiceName }}</td>
                <td>{{ $itemOrServiceDescription }}</td>
                <td>{{ $itemOrServicePrice->format() }}</td>
                <td style="text-align: center">{{ $itemOrServiceQuantity }}</td>
                <td>{{ $itemOrServicePriceTotalPrice->format() }}</td>
                <td>{{ $itemOrServiceVatPercentage }}%</td>
                {{-- <td>
                    <table class="pricing-grid">
                        <tr>
                            <td style="width: 33%;">{{ $itemOrServicePrice->format() }}</td>
                            <td style="width: 33%;">{{ $itemOrServiceQuantity }}</td>
                            <td style="width: 20%;">{{ $itemOrServicePriceTotalPrice->format() }}</td>
                            <td style="width: 33%;">{{ $itemOrServiceVatPercentage }}%</td>
                        </tr>
                    </table>
                </td> --}}
            </tr>
        @endforeach

        <tr class="heading">
            <td colspan="6" style="text-align: center">{{ __('quotations.total') }}</td>
        </tr>
        <tr>
            <td></td>
            <td style="text-align: center" colspan="3">{{ __('quotations.subtotal') }}</td>
            <td colspan="2">{{ ((new \App\Plugins\Moneys())->setAmount($quotation->subTotal_price))->format() }}</td>
            {{-- <td>
                <table class="pricing-grid">
                    <tr>
                        <td style="width: 33%;"></td>
                        <td style="width: 33%;">{{ __('quotations.subtotal') }}</td>
                        <td>{{ ((new \App\Plugins\Moneys())->setAmount($quotation->subTotal_price))->format() }}</td>
                    </tr>
                </table>
            </td> --}}
        </tr>

        @php
            $vatsAggregated = [];

            foreach ($itemsAndServices as $itemOrService) {
                $itemOrServicePrice = match (true) {
                    $itemOrService instanceof Item => $itemOrService->product->price['gross_ppp'],
                    $itemOrService instanceof Service => $itemOrService->price->amount()
                };

                $itemOrServiceGrossPrice = $itemOrServicePrice * $itemOrService->pivot->getAttribute('qty');

                $itemOrServiceVatPercentageAsString = (string) (float) match (true) {
                    $itemOrService instanceof Item => $itemOrService->product->price['vat'],
                    $itemOrService instanceof Service => $itemOrService->getAttribute('vat')
                };

                if (!array_key_exists($itemOrServiceVatPercentageAsString, $vatsAggregated)) {
                    $vatsAggregated[$itemOrServiceVatPercentageAsString] = $itemOrServiceGrossPrice;
                } else {
                    $vatsAggregated[$itemOrServiceVatPercentageAsString] += $itemOrServiceGrossPrice;
                }
            }
        @endphp
        @foreach ($vatsAggregated as $vatPercentageAsString => $pricesAggregatedRaw)
            <tr>
                <td></td>
                <td colspan="3" style="text-align: center">{{ $vatPercentageAsString }}% {{ __('quotations.vat') }}
                    over {{ ((new \App\Plugins\Moneys())->setAmount($pricesAggregatedRaw))->format() }}</td>
                <td colspan="2">{{ ((new \App\Plugins\Moneys())->setAmount($pricesAggregatedRaw))->newFromTax((float) $vatPercentageAsString)->format() }}</td>
                {{-- <td colspan="2">
                    <table class="pricing-grid">
                        @foreach ($vatsAggregated as $vatPercentageAsString => $pricesAggregatedRaw)
                            <tr>
                                <td style="width: 87%; text-align: right;">{{ $vatPercentageAsString }}% {{ __('quotations.vat') }} over {{ ((new \App\Plugins\Moneys())->setAmount($pricesAggregatedRaw))->format() }}</td>
                                <td style="width: 13%;">{{ ((new \App\Plugins\Moneys())->setAmount($pricesAggregatedRaw))->newFromTax((float) $vatPercentageAsString)->format() }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td> --}}
            </tr>
        @endforeach
        <tr class="heading">
            <td></td>
            <td colspan="3" style="text-align: center">{{ __('quotations.grand_total') }}</td>
            <td colspan="2">{{ ((new \App\Plugins\Moneys())->setAmount($quotation->getAttribute('total_price')))->format() }}</td>
            {{-- <td>
                <table class="pricing-grid">
                    <tr>
                        <td style="width: 33%;"></td>
                        <td style="width: 33%;">{{ __('quotations.grand_total') }}</td>
                        <td>{{ ((new \App\Plugins\Moneys())->setAmount($quotation->getAttribute('total_price')))->format() }}</td>
                    </tr>
                </table>
            </td> --}}
        </tr>
    </table>
</div>
</body>
</html>

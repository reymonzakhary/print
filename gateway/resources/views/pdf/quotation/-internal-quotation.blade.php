@php
    /**
     * This template is based on the Simple HTML Invoice Template by SparkSuite.
     * Source: https://github.com/sparksuite/simple-html-invoice-template
     */

    use App\Actions\PriceAction\CalculationAction;
    use App\Models\Tenants\Item;
    use App\Models\Tenants\Service;
    use App\Plugins\Moneys;
    use Carbon\Carbon;
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
    <title>{{ __('quotations.quotation')}}</title>


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
            background-color: #eeeeee;
            border-left: 4px solid #c8c8c8;
            border-radius: 6px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            color: #7c7c7c;
            margin-bottom: 1rem;
            padding: 1rem 1.5rem;
            position: relative;
            margin-left: auto;
            margin-right: auto;
        }

        .alert p {
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 0;
            margin-top: 0
        }

        .pricing-grid {
            width: 100%;
        }

        .pricing-grid tr td {
            border-bottom: none !important;
            padding: 0px !important;
        }

        body > div.invoice-box > table > tbody > tr > td:first-child {
            width: 60%;
        }
    </style>
</head>
<body>
@if(!$hideExpirationMessage && $quotation->getAttribute('expire_at'))
    <article class="alert">
        <p>
            {{ __('quotations.expire_at_message') }}
            {{ Carbon::createFromTimeString($quotation->getAttribute('expire_at'))->toDateTimeString() }}
        </p>
    </article>
@endif
<div class="invoice-box">
    <table cellpadding="0" cellspacing="0" class="invoice-table">
        <tr class="top">
            <td colspan="2">
                <table style="width: 100%;">
                    <tr>
                        <td style="text-align: left;">
                            @if(!empty($settings['logo']))
                                <img
                                    src="{{ $settings['logo'] }}"
                                    style="width: 100%; max-width: 300px"
                                />
                            @endif
                            <br/>
                            <br/>
                            <br/>
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
                        <td style="text-align: right;">
                            @if(isset($supplierData->company_name))
                                {{ $supplierData->company_name }}<br/><br/>
                            @endif

                            @if($contextAddress)
                                {{ $contextAddress->getAttribute('number') }} <br/>
                                {{ $contextAddress->getAttribute('zip_code') }}
                                , {{ $contextAddress?->getAttribute('city') }}<br/>
                                {{ $contextAddress->pivot->getAttribute('phone_number') }} <br/>
                            @endif

                            @if(isset($supplierData->email))
                                {{ $supplierData->email }} <br/><br/>
                            @endif

                            @if(isset($supplierData->coc))
                                {{ __('COC') }}: {{ $supplierData->coc }} <br/>
                                {{ $supplierData->tax_nr ?? '' }} <br/>
                            @endif

                            @if(isset($supplierData->IBAN))
                                <br/>
                                {{ $supplierData->IBAN }}
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
                                    <td>{{ $quotation->getAttribute('created_at') ? (new DateTime($quotation->getAttribute('created_at')))->format('d-m-Y h:i:s') : "-" }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('quotations.due') }}:</td>
                                    <td>{{ $quotation->getAttribute('expire_at') ? (new DateTime($quotation->getAttribute('expire_at')))->format('d-m-Y h:i:s') : "-" }}</td>
                                </tr>
                            </table>
                        </td>

                        <td style="text-align: right;">
                            @if(count($quotation->address) > 0)
                                <br/>
                                <br/>
                                {{ $quotation->address[0]->pivot->company_name !== __("quotations.no_company") ? $quotation->address[0]->pivot->company_name : "" }}
                                <br/>
                                {{ $quotation->orderedBy?->profile->first_name }} {{ $quotation->orderedBy?->profile->last_name }}
                                <br/>
                                {{ $quotation->orderedBy?->email }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="heading">
            <td>{{ __('quotations.name') }}</td>
            <td>{{ __('quotations.description') }}</td>
            <td>
                <table class="pricing-grid">
                    <tr>
                        <td style="width: 33%;">{{ __('quotations.amount') }}</td>
                        <td style="width: 33%;">{{ __('quotations.quantity') }}</td>
                        <td>{{ __('quotations.total') }}</td>
                        <td>{{ __('quotations.vat') }}</td>
                    </tr>
                </table>
            </td>
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
                    $itemOrService instanceof Item => (new Moneys())->setAmount($itemOrService->product->price['gross_ppp']),
                    $itemOrService instanceof Service => $itemOrService->price
                };

                $itemOrServiceVatPercentage = match (true) {
                    $itemOrService instanceof Item => $itemOrService->product->price['vat'],
                    $itemOrService instanceof Service => $itemOrService->getAttribute('vat')
                };

                $itemOrServiceQuantity = $itemOrService->pivot->getAttribute('qty');

                $itemOrServicePriceTotalPrice = (new Moneys())->setAmount($itemOrServicePrice->amount() * $itemOrServiceQuantity);
            @endphp

            <tr class="item">
                <td>{{ $itemOrServiceName }}</td>
                <td>{{ $itemOrServiceDescription }}</td>
                <td>
                    <table class="pricing-grid">
                        <tr>
                            <td style="width: 33%;">{{ $itemOrServicePrice->format() }}</td>
                            <td style="width: 33%;">{{ $itemOrServiceQuantity }}</td>
                            <td style="width: 20%;">{{ $itemOrServicePriceTotalPrice->format() }}</td>
                            <td style="width: 33%;">{{ $itemOrServiceVatPercentage }}%</td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endforeach

        <tr class="heading">
            <td>{{ __('quotations.total') }}</td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>
                <table class="pricing-grid">
                    <tr>
                        <td style="width: 33%;"></td>
                        <td style="width: 33%;">{{ __('quotations.subtotal') }}</td>
                        <td>{{ ((new Moneys())->setAmount($quotation->subTotal_price))->format() }}</td>
                    </tr>
                </table>
            </td>
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

        <tr>
            <td colspan="2">
                <table class="pricing-grid">
                    @foreach ($vatsAggregated as $vatPercentageAsString => $pricesAggregatedRaw)
                        <tr>
                            <td style="width: 87%; text-align: right;">{{ $vatPercentageAsString }}
                                % {{ __('quotations.vat') }}
                                over {{ ((new Moneys())->setAmount($pricesAggregatedRaw))->format() }}</td>
                            <td style="width: 13%;">{{ ((new Moneys())->setAmount($pricesAggregatedRaw))->newFromTax((float) $vatPercentageAsString)->format() }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>

        <tr class="heading">
            <td></td>
            <td>
                <table class="pricing-grid">
                    <tr>
                        <td style="width: 33%;"></td>
                        <td style="width: 33%;">{{ __('quotations.grand_total') }}</td>
                        <td>{{ ((new Moneys())->setAmount($quotation->getAttribute('total_price')))->format() }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>

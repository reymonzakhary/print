@php
    /**
     * This template is based on the Simple HTML Invoice Template by SparkSuite.
     * Source: https://github.com/sparksuite/simple-html-invoice-template
     */

    use App\Facades\Settings;
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

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:last-child

        )
        {
            text-align: right
        ;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
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

        .invoice-box.rtl table {
            text-align: right;
        }

        .invoice-box.rtl table tr td:nth-child(2) {
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
    </style>
</head>
@php
    $vat = Settings::vat()->value;
@endphp
<body>
<article class="alert">
    <p>
        Note: This quotation will expire after: {{ Carbon::createFromTimeString($quotation->expire_at)->diffForHumans() }}
    </p>
</article>
<div class="invoice-box">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="3">
                <table>
                    <tr>
                        <td class="title">
                            <img
                                src="data:image/png;base64, {{ $settings['quotation_logo'] }}"
                                style="width: 100%; max-width: 300px"
                            />
                        </td>

                        <td>
                            {{ __('quotations.quotation')}}: #{{ $quotation->getAttribute('id') ?? "N/A" }}<br/>
                            {{ __('quotations.created') }}: {{ $quotation->created_at ?? "N/A" }}<br/>
                            {{ __('quotations.due') }}: {{ $quotation->expire_at ?? "N/A" }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="information">
            <td colspan="3">
                <table>
                    <tr>
                        <td>
                            <br>
                            @php
                                $customerDefaultAddress = $quotation->orderedBy->invoiceAddress();
                            @endphp
                            {{ $customerDefaultAddress->getAttribute('number') }}<br/>
                            {{ sprintf('%s, %s', $customerDefaultAddress->getAttribute('city'), $customerDefaultAddress->getAttribute('zip_code')) }}
                        </td>

                        <td style="text-align: right;">
                            @if(count($quotation->address) > 0)
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
            <td>{{ __('quotations.item') }}</td>
            <td></td>
            <td>{{ __('quotations.price') }}</td>
        </tr>

        @foreach($quotation->items as $key => $item)
            @if($item->product)
                @php
                    $product = $item->product;
                @endphp
                <tr class="item">
                    <td>{{ $product['category']['name'] }}</td>
                    <td></td>
                    <td>
                    @if(isset($product->price['gross_price']))
                        {{ (new \App\Plugins\Moneys())->setAmount($product->price['gross_price'])->format() }}
                        @else
                        {{ (new \App\Plugins\Moneys())->setAmount(0)->format() }}
                    @endif
                    </td>
                </tr>
            @endif
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        @if(count($quotation->services) > 0)
            <tr class="heading">
                <td>{{ __('quotations.service') }}</td>
                <td>{{ __('quotations.description') }}</td>
                <td>{{ __('quotations.price') }}</td>
            </tr>
            @foreach($quotation->services as $key => $item)
                <tr class="item">
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->price }}</td>
                </tr>
            @endforeach
        @endif
        <tr class="heading">
            <td>{{ __('quotations.total') }}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>{{ __('quotations.subtotal') }}</td>
            <td></td>
            <td>{{$quotation->price->format() }}</td>
        </tr>
        <tr>
            <td>{{$vat}}% {{ __('quotation.vat') }}:</td>
            <td></td>
            <td>{{ $quotation->price->newFromTax($vat) }}</td>
        </tr>
        </tr>

        <tr class="heading">
            <td>{{ __('quotations.grand_total') }}</td>
            <td></td>
            <td>{{ (new \App\Plugins\Moneys())->setAmount($quotation->price->amount())->add($quotation->price->newFromTax($vat)->amount())->format() }}</td>
        </tr>
    </table>
</div>
</body>
</html>

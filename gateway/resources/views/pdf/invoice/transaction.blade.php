@php
    use App\Plugins\Moneys;
    use Carbon\Carbon;

    /*
     * General Data
     */
    $custom_field = $transaction->getAttribute('custom_field');
    $customer_data = $custom_field->pick('customer');
    $supplier_data = $custom_field->pick('supplier');
    $products = $custom_field->pick('products');

    /*
     * Company Data
     */
    $company_name = $customer_data['company_name'];
    $company_representative = $customer_data['full_name'];
    $company_address = $customer_data['address_data']['address'] . ' ' . $customer_data['address_data']['number'];
    $company_zipcode = $customer_data['address_data']['zip_code'];
    $company_city = $customer_data['address_data']['city'];

    /*
     * Invoice Data
     */
    $invoice_number = $transaction->getAttribute('invoice_nr');
    $invoice_date = $transaction->getAttribute('invoice_date');
    $invoice_due_date = $transaction->getAttribute('due_date');

    /*
     * Payment Terms
     */
    $invoice_date_obj = Carbon::parse($invoice_date);
    $invoice_due_date_obj = Carbon::parse($invoice_due_date);
    $payment_terms_days = $invoice_due_date_obj->diffInDays($invoice_date_obj);
    $payment_terms = "{$payment_terms_days} dagen";

    /*
     * Vat Amounts
     */
    $vat_amounts = array_map(
        static function ($vatValue): Moneys {
            return (new Moneys())->setAmount($vatValue * 100);
        },
        $custom_field->pick('vats')
    );

    $total_excl_vat = (new Moneys())->setAmount($custom_field->pick('total_ex'));
    $total_incl_vat = (new Moneys())->setAmount($custom_field->pick('total_incl_vat'));

    /*
     * Appearance Settings
     */
    $currency_symbol = $total_excl_vat->getCurrency();
    $letterhead_size = $settings['invoice_letterhead_size'] === 'A4' ? 'A4' : 'letter';
    $direction = $settings['invoice_customer_address_position_direction'];
    $offset = $settings['invoice_customer_address_position'];
    $font_size = $settings['invoice_font_size'];
    $font_family = $settings['invoice_font'];
    $background = $settings['invoice_background'];
    $logo = $settings['invoice_logo'];
    $logo_position = $settings['invoice_logo_position'];
    $logo_width = $settings['invoice_logo_width'];

    /*
     * The product row limit is calculated by dividing the rowHeight (2.5 * fontSize) by the height of the first page table.
     */
    $amount_of_items = count($products);
    $first_table_height = 418 + 25 * (16 - $font_size) - $offset;
    $extra_table_height = 518;
    $first_product_row_limit = floor($first_table_height / (2.5 * $font_size));
    $extra_product_row_limit = floor($extra_table_height / (2.5 * $font_size)) - 2;
@endphp

@extends('pdf.invoice.layouts.main')

@section('content')
    @include('pdf.invoice.partials.header')

    @include('pdf.invoice.partials.details')

    @if ($amount_of_items <= $first_product_row_limit)
        @include('pdf.invoice.partials.table', [
            'items' => array_slice($products, 0, $first_product_row_limit),
            'is_extra_page' => false,
        ])

        @include('pdf.invoice.partials.footer')
    @else
        @include('pdf.invoice.partials.table', [
            'items' => array_slice($products, 0, $first_product_row_limit),
            'is_extra_page' => false,
        ])

        @for ($i = 7; $i < $amount_of_items; $i += $extra_product_row_limit)
            <table class="totals">
                <tr>
                    <td style="width: 81%; text-align: right; padding-right: 1rem;">{{ __('invoices.sub_total_excl_vat') }}
                    </td>
                    <td class="currency-cell" style="width: 19%;"><span
                            class="currency-symbol">{{ $currency_symbol }}</span>{{ number_format(array_sum(array_column(array_slice($products, 0, $i), 'total')), 2, ',', '.') }}
                    </td>
                </tr>
            </table>
            <p class="footer-info">{{ __('next_page_message') }}</p>
            <div class="page_break"></div>

            @include('pdf.invoice.partials.header')

            @include('pdf.invoice.partials.table', [
                'items' => array_slice($products, $i, $extra_product_row_limit),
                'is_extra_page' => true,
            ])
        @endfor

        @include('pdf.invoice.partials.footer')
    @endif
@endsection

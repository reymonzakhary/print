<?php

namespace App\Plugins;

/**
 * PriceFormatter - Complete price formatting for product/supplier data
 *
 * Handles ALL price fields including shipping costs, VAT variations, and high-precision calculations
 *
 * @package App\Plugins
 */
class PriceFormatter
{
    /**
     * Format a single price item or array of price items
     *
     * @param array $data Single item or array of items
     * @param array $options Additional options (id, pm, etc.)
     * @return array Formatted data with display_ fields
     */
    public static function format(array $data, array $options = []): array
    {
        // Check if this is an array of items (indexed array)
        if (self::isIndexedArray($data)) {
            return array_map(fn($item) => self::formatSingleItem($item, $options), $data);
        }

        // Single item
        return self::formatSingleItem($data, $options);
    }

    /**
     * Format a single price item with ALL display fields
     *
     * Complete implementation matching your exact usage pattern
     *
     * @param array $data Price data
     * @param array $options Additional options
     * @return array Fully formatted data
     */
    private static function formatSingleItem(array $data, array $options = []): array
    {
        $vat = sanitizeToInt($data['vat'] ?? 0);

        return [
            // =============================================
            // META & BASIC INFO
            // =============================================
            'id' => $options['id'] ?? $data['id'] ?? null,
            'pm' => $options['pm'] ?? $data['pm'] ?? '',
            'qty' => $data['qty'] ?? 0,
            'dlv' => $data['dlv'] ?? [],
            'vat' => $vat,

            // =============================================
            // PRICE (p) - Total price
            // =============================================
            'p' => $data['p'] ?? 0,
            'display_p' => moneys()->setAmount(optional($data)['p'])->format(),//Moneys::display($data['p'] ?? 0),

            // =============================================
            // PRICE PER PIECE (ppp) - High precision 5 decimals
            // =============================================
            'ppp' => $data['ppp'] ?? 0,
            'display_ppp' => Moneys::display($data['ppp'] ?? 0, precision: 5),

            // =============================================
            // VAT CALCULATIONS
            // =============================================

            // VAT on selling_price_ex (vat_p)
            'vat_p' =>  moneys()->setAmount($data['selling_price_ex'] ?? 0)->setTax(optional($data)['vat']??0)->amount(onlyTax: true),
            'display_vat_p' =>  moneys()->setAmount($data['selling_price_ex'] ?? 0)->setTax(optional($data)['vat']??0)->format(onlyTax: true),

            // VAT on selling price with shipping (vat_total_p)
            'vat_total_p' =>  moneys()->setAmount($data['selling_price_inc_shipping'] ?? 0)->setTax(optional($data)['vat']??0)->amount(onlyTax: true),
            'display_vat_total_p' => moneys()->setAmount($data['selling_price_inc_shipping'] ?? 0)->setTax(optional($data)['vat']??0)->format(onlyTax: true),

            // VAT per piece (vat_ppp) - High precision 5 decimals
            'vat_ppp' => $data['vat_ppp'] ?? 0,
            'display_vat_ppp' =>  moneys()->setAmount($data['vat_ppp'] ?? 0)->format(),

            // =============================================
            // PROFIT
            // =============================================
            'profit' => $data['profit'] ?? 0,
            'display_profit' => moneys()->setAmount($data['profit'] ?? 0)->format(),

            // =============================================
            // GROSS PRICES
            // =============================================
            'gross_price' => $data['gross_price'] ?? 0,
            'display_gross_price' => moneys()->setAmount($data['gross_price'] ?? 0)->format(),

            'gross_ppp' => $data['gross_ppp'] ?? 0,
            'display_gross_ppp' => moneys()->setAmount($data['gross_ppp'] ?? 0)->format(),

            // =============================================
            // SELLING PRICES (without shipping)
            // =============================================

            // Selling price EX VAT (raw value - just divided)
            'selling_price_ex' => $data['selling_price_ex'] ?? 0,
            'display_selling_price_ex' => moneys()->setAmount(optional($data)['selling_price_ex']??0)->format(),

            // Selling price INC VAT (calculated from ex price)
            'selling_price_inc' => $data['selling_price_ex'] ?? 0,
            'display_selling_price_inc' => moneys()->setAmount(optional($data)['selling_price_ex']??0)->format(),

            // =============================================
            // SELLING PRICES WITH SHIPPING
            // =============================================

            // Raw value with shipping (pass-through or calculated)
            'selling_price_inc_shipping' => $data['selling_price_inc_shipping'] ?? 0,

            // Selling price with shipping EX VAT
            'selling_price' => $data['selling_price_inc_shipping'] ?? 0,
            'display_selling_price' => moneys()->setAmount(optional($data)['selling_price_inc_shipping']??0)->format(),

            // Selling price with shipping INC VAT (total)
            'selling_price_total' => $data['selling_price_inc_shipping'] ?? 0,
            'display_selling_price_total' =>  moneys()->setAmount($data['selling_price_inc_shipping'] ?? 0)->format(),

            // =============================================
            // SHIPPING COST
            // =============================================
            'shipping_cost' => $data['shipping_cost'] ?? 0,
            'display_shipping_cost' => moneys()->setAmount($data['shipping_cost'] ?? 0)->format(),

            // =============================================
            // ADDITIONAL INFO
            // =============================================
            'discount' => $data['discount'] ?? [],
            'margins' => self::formatMargins($data['margins'] ?? []),
        ];
    }

    /**
     * Format margins data with display value
     *
     * @param array $margins Margins data
     * @return array Formatted margins
     */
    private static function formatMargins(array $margins): array
    {
        if (empty($margins)) {
            return [];
        }

        $value = $margins['value'] ?? 0;
        $type = $margins['type'] ?? 'fixed';

        return [
            'value' => $value,
            'type' => $type,
            'display_value' => $type === 'percentage'
                ? $value  // Show as is for percentage (100 = 100%)
                : Moneys::display($value), // Format as currency for fixed
        ];
    }

    /**
     * Check if array is indexed (not associative)
     *
     * @param array $array Array to check
     * @return bool True if indexed array
     */
    private static function isIndexedArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Legacy method - kept for backward compatibility
     *
     * @param array $data Price data
     * @return array Formatted data
     */
    public static function formatAll(array $data): array
    {
        return self::format($data);
    }
}

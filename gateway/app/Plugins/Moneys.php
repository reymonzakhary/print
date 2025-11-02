<?php

namespace App\Plugins;

use App\Facades\Settings;
use App\Services\Margins\MarginService;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Log;
use RuntimeException;

/**
 * Moneys - Advanced Money Handling Class
 *
 * Provides comprehensive money handling with multi-currency support,
 * VAT calculations, locale-aware formatting, and high-precision arithmetic.
 *
 * @package App\Plugins
 * @version 2.0.0
 *
 * @example Basic Usage
 * ```php
 * // Simple formatting
 * $money = moneys()->setAmount(1500)->format();
 * // Output: "USD 1,500.00" or "1,500.00 د.إ" (Arabic)
 *
 * // With VAT
 * $money = moneys()
 *     ->setTax(15)
 *     ->setAmount(1000)
 *     ->format(inc: true);
 * // Output: "USD 1,150.00" (1000 + 15% VAT)
 *
 * // VAT only
 * $vat = moneys()
 *     ->setTax(15)
 *     ->setAmount(1000)
 *     ->format(inc: false, onlyTax: true);
 * // Output: "USD 150.00"
 *
 * // Currency-specific
 * $money = moneys()
 *     ->setCurrency('SAR')
 *     ->setAmount(2500)
 *     ->format();
 * // Output: "SAR 2,500.00" (English) or "2,500.00 ﷼" (Arabic)
 * ```
 *
 * @example Advanced Usage
 * ```php
 * // Chain operations
 * $total = moneys()
 *     ->setAmount(1000)
 *     ->add(500)
 *     ->multiply(2)
 *     ->subtract(200)
 *     ->format();
 * // Output: "USD 2,800.00"
 *
 * // Factory methods
 * $money = Moneys::fromAmount(1500, 'EUR');
 * $zero = Moneys::zero('GBP');
 *
 * // Comparisons
 * if ($money1->isGreaterThan($money2)) { ... }
 * if ($total->isZero()) { ... }
 *
 * // Receipts
 * $receipt = moneys()
 *     ->setTax(15)
 *     ->setAmount(1000)
 *     ->receipt();
 * // Returns: ['subtotal' => 1000, 'vat' => 150, 'total' => 1150]
 * ```
 *
 * @example Real-world Usage
 * ```php
 * // Product pricing with VAT
 * $price = moneys()
 *     ->setTax(sanitizeToInt($product['vat'] ?? 0))
 *     ->setAmount(optional($product)['selling_price_inc_shipping'])
 *     ->format(inc: true);
 *
 * // VAT amount only
 * $vatAmount = moneys()
 *     ->setTax(sanitizeToInt($this['vat'] ?? 0))
 *     ->setAmount(optional($this)['selling_price_inc_shipping'])
 *     ->format(inc: false, onlyTax: true);
 *
 * // Multi-currency with custom locale
 * $price = moneys()
 *     ->setCurrency('AED', 'ar')
 *     ->setAmount($amount)
 *     ->format();
 * ```
 */
class Moneys
{
    // =========================================================================
    // CONSTANTS
    // =========================================================================

    /**
     * High precision factor for VAT calculations (10^11)
     * Used to maintain precision when calculating VAT on large amounts
     */
    private const int PRECISION_FACTOR = 100000000000;

    /**
     * Base percentage value for calculations
     */
    private const int PERCENTAGE_BASE = 100;

    /**
     * Maximum allowed amount (1 trillion - 0.01)
     */
    private const float MAX_AMOUNT = 999999999999.99;

    /**
     * Minimum allowed amount (-1 trillion + 0.01)
     */
    private const float MIN_AMOUNT = -999999999999.99;

    /**
     * Precision levels for different use cases
     */
    private const int PRECISION_STANDARD = 2;    // Standard currency: €843.38
    private const int PRECISION_HIGH = 5;        // Per-piece: €8.43810
    private const int PRECISION_ULTRA = 10;      // Scientific: €0.0843810000

    // =========================================================================
    // PROPERTIES
    // =========================================================================

    /**
     * The monetary amount being processed
     * @var float
     */
    private float $amount = 0;

    /**
     * The currency symbol for display (e.g., "$", "€", "د.إ")
     * @var string
     */
    public string $currency;

    /**
     * The ISO currency code (e.g., "USD", "EUR", "SAR")
     * @var string
     */
    public string $currency_iso;

    /**
     * Number of decimal places for rounding (default: 2)
     * @var int
     */
    private int $precision;

    /**
     * VAT/Tax percentage (e.g., 15 for 15%)
     * @var float|int
     */
    private float|int $percentage = 0;

    /**
     * Number of decimal places in the original amount (default: 2)
     * @var int
     */
    private int $decimal;

    /**
     * Cached decimal multiplier (e.g., 100 for 2 decimals, 1000 for 3)
     * @var int
     */
    private int $decimalMultiplier;

    /**
     * Whether VAT is included in calculations
     * @var bool
     */
    private bool $inc = false;

    /**
     * Cached formatted value to avoid recalculation
     * @var string|null
     */
    private ?string $cachedFormat = null;

    /**
     * Last cache key for format validation
     * @var string|null
     */
    private ?string $lastCacheKey = null;

    // =========================================================================
    // CURRENCY SYMBOLS
    // =========================================================================

    /**
     * Currency symbols with locale support
     *
     * Supported locales:
     * - 'en': English (and de, nl, be, fr as fallback)
     * - 'ar': Arabic (ara)
     *
     * Format: ['CURRENCY_CODE' => ['en' => 'Symbol', 'ar' => 'Arabic Symbol']]
     *
     * @var array<string, array<string, string>>
     */
    private static array $currencySymbols = [
        // Major World Currencies
        'USD' => ['en' => '$', 'ar' => '$'],
        'EUR' => ['en' => '€', 'ar' => '€'],
        'GBP' => ['en' => '£', 'ar' => '£'],
        'JPY' => ['en' => '¥', 'ar' => '¥'],
        'CHF' => ['en' => 'CHF', 'ar' => 'CHF'],
        'CNY' => ['en' => '¥', 'ar' => '¥'],

        // Commonwealth & Asia Pacific
        'AUD' => ['en' => 'A$', 'ar' => 'A$'],
        'CAD' => ['en' => 'C$', 'ar' => 'C$'],
        'NZD' => ['en' => 'NZ$', 'ar' => 'NZ$'],
        'SGD' => ['en' => 'S$', 'ar' => 'S$'],
        'HKD' => ['en' => 'HK$', 'ar' => 'HK$'],

        // Nordic Currencies
        'SEK' => ['en' => 'kr', 'ar' => 'kr'],
        'NOK' => ['en' => 'kr', 'ar' => 'kr'],
        'DKK' => ['en' => 'kr', 'ar' => 'kr'],
        'ISK' => ['en' => 'kr', 'ar' => 'kr'],

        // Middle East & North Africa
        'AED' => ['en' => 'AED', 'ar' => 'د.إ'],    // UAE Dirham
        'SAR' => ['en' => 'SAR', 'ar' => '﷼'],      // Saudi Riyal
        'QAR' => ['en' => 'QAR', 'ar' => 'ر.ق'],    // Qatari Riyal
        'KWD' => ['en' => 'KWD', 'ar' => 'د.ك'],    // Kuwaiti Dinar
        'BHD' => ['en' => 'BHD', 'ar' => 'ب.د'],    // Bahraini Dinar
        'OMR' => ['en' => 'OMR', 'ar' => 'ر.ع'],    // Omani Rial
        'JOD' => ['en' => 'JOD', 'ar' => 'د.ا'],    // Jordanian Dinar
        'EGP' => ['en' => 'EGP', 'ar' => 'ج.م'],    // Egyptian Pound

        // South Asia
        'INR' => ['en' => '₹', 'ar' => '₹'],        // Indian Rupee
        'PKR' => ['en' => 'Rs', 'ar' => '₨'],       // Pakistani Rupee
        'BDT' => ['en' => '৳', 'ar' => '৳'],         // Bangladeshi Taka

        // Southeast Asia
        'THB' => ['en' => '฿', 'ar' => '฿'],        // Thai Baht
        'MYR' => ['en' => 'RM', 'ar' => 'RM'],      // Malaysian Ringgit
        'IDR' => ['en' => 'Rp', 'ar' => 'Rp'],      // Indonesian Rupiah
        'PHP' => ['en' => '₱', 'ar' => '₱'],        // Philippine Peso
        'VND' => ['en' => '₫', 'ar' => '₫'],        // Vietnamese Dong

        // East Asia
        'KRW' => ['en' => '₩', 'ar' => '₩'],        // South Korean Won

        // Other Major Currencies
        'TRY' => ['en' => '₺', 'ar' => '₺'],        // Turkish Lira
        'RUB' => ['en' => '₽', 'ar' => '₽'],        // Russian Ruble
        'BRL' => ['en' => 'R$', 'ar' => 'R$'],      // Brazilian Real
        'ZAR' => ['en' => 'R', 'ar' => 'R'],        // South African Rand
        'MXN' => ['en' => 'MX$', 'ar' => 'MX$'],    // Mexican Peso

        // European Currencies
        'PLN' => ['en' => 'zł', 'ar' => 'zł'],      // Polish Zloty
        'CZK' => ['en' => 'Kč', 'ar' => 'Kč'],      // Czech Koruna
        'HUF' => ['en' => 'Ft', 'ar' => 'Ft'],      // Hungarian Forint
        'RON' => ['en' => 'lei', 'ar' => 'lei'],    // Romanian Leu
        'BGN' => ['en' => 'лв', 'ar' => 'лв'],      // Bulgarian Lev
        'HRK' => ['en' => 'kn', 'ar' => 'kn'],      // Croatian Kuna

        // Middle East (Other)
        'ILS' => ['en' => '₪', 'ar' => '₪'],        // Israeli Shekel

        // Latin America
        'CLP' => ['en' => 'CLP$', 'ar' => 'CLP$'],  // Chilean Peso
        'COP' => ['en' => 'COP$', 'ar' => 'COP$'],  // Colombian Peso
        'PEN' => ['en' => 'S/', 'ar' => 'S/'],      // Peruvian Sol
        'ARS' => ['en' => '$', 'ar' => '$'],        // Argentine Peso
        'UYU' => ['en' => '$U', 'ar' => '$U'],      // Uruguayan Peso
    ];

    // =========================================================================
    // CONSTRUCTOR & SETUP
    // =========================================================================

    /**
     * Initialize a new Moneys instance with default settings
     *
     * Sets default precision (2), decimal places (2), and currency from system settings
     */
    public function __construct()
    {
        $this->setPrecision();
        $this->setDecimal();
        $this->setCurrency();
    }

    // =========================================================================
    // STATIC FACTORY METHODS
    // =========================================================================

    /**
     * Create a new Moneys instance from an amount
     *
     * @param float|int|string $amount The amount to set
     * @param string|null $currency Currency code (e.g., 'USD', 'EUR')
     * @return static
     *
     * @example
     * ```php
     * $money = Moneys::fromAmount(1500, 'EUR');
     * echo $money->format(); // "EUR 1,500.00"
     * ```
     */
    public static function fromAmount(
        float|int|string $amount,
        ?string $currency = null
    ): static
    {
        return (new static())->setAmount($amount)->setCurrency($currency);
    }

    /**
     * Create a new Moneys instance from cents/smallest unit
     *
     * @param int $cents Amount in cents (e.g., 15000 = $150.00)
     * @param string|null $currency Currency code
     * @return static
     *
     * @example
     * ```php
     * $money = Moneys::fromCents(15000, 'USD');
     * echo $money->format(); // "USD 150.00"
     * ```
     */
    public static function fromCents(
        int $cents,
        ?string $currency = null
    ): static
    {
        return (new static())->setAmount($cents)->setCurrency($currency);
    }

    /**
     * Create a new Moneys instance with zero amount
     *
     * @param string|null $currency Currency code
     * @return static
     *
     * @example
     * ```php
     * $money = Moneys::zero('GBP');
     * echo $money->format(); // "£ 0.00"
     * ```
     */
    public static function zero(
        ?string $currency = null
    ): static
    {
        return (new static())->setAmount(0)->setCurrency($currency);
    }

    /**
     * Get all supported currencies
     *
     * @return array<string> Array of currency codes
     *
     * @example
     * ```php
     * $currencies = Moneys::getSupportedCurrencies();
     * // ['USD', 'EUR', 'GBP', 'SAR', ...]
     * ```
     */
    public static function getSupportedCurrencies(): array
    {
        return array_keys(self::$currencySymbols);
    }

    /**
     * Get all currency symbols (legacy method)
     *
     * @return array<string, array<string, string>>
     * @deprecated Use getSupportedCurrencies() instead
     */
    public static function getCurrencies(): array
    {
        return self::$currencySymbols;
    }

    /**
     * Get currency symbol for a specific locale
     *
     * @param string $currencyCode Currency code (e.g., 'SAR')
     * @param string|null $locale Locale code (en, ar, de, nl, be, fr, ara)
     * @return string Currency symbol
     *
     * @example
     * ```php
     * echo Moneys::getSymbol('SAR', 'en'); // "SAR"
     * echo Moneys::getSymbol('SAR', 'ar'); // "﷼"
     * echo Moneys::getSymbol('AED', 'ar'); // "د.إ"
     * ```
     */
    public static function getSymbol(
        string $currencyCode,
        ?string $locale = null
    ): string
    {
        $locale = $locale ?: app()->getLocale();
        $localeKey = self::normalizeLocaleStatic($locale);

        if (!isset(self::$currencySymbols[$currencyCode])) {
            return $currencyCode;
        }

        return self::$currencySymbols[$currencyCode][$localeKey]
            ?? self::$currencySymbols[$currencyCode]['en']
            ?? $currencyCode;
    }

    /**
     * Get currency symbols by language
     *
     * @return Collection Collection of currency symbols mapped by language
     */
    public static function getCurrencyByLang(): Collection
    {
        $localeKey = self::normalizeLocaleStatic(app()->getLocale());
        return collect(self::$currencySymbols)->map(function ($symbol, $k) use ($localeKey) {
            return $symbol[$localeKey] = $symbol['en'];
        });

    }
    // =========================================================================
    // CONFIGURATION METHODS
    // =========================================================================

    /**
     * Set the currency for this money instance
     *
     * @param string|null $currency Currency code (e.g., 'USD', 'SAR', 'EUR')
     * @param string|null $locale Locale for symbol selection (en, ar, de, nl, be, fr)
     * @return $this
     *
     * @example
     * ```php
     * $money->setCurrency('AED'); // Uses app locale
     * $money->setCurrency('AED', 'ar'); // Force Arabic symbol
     * $money->setCurrency('SAR', 'en'); // Force English symbol
     * ```
     */
    public function setCurrency(
        ?string $currency = null,
        ?string $locale = null
    ): self
    {
        // Get currency ISO from system settings
        $this->currency_iso = Settings::from('system')->currency()?->value ?? 'USD';

        // Use provided currency or fall back to system currency
        $currencyCode = $currency ?: $this->currency_iso;

        // Validate currency code
        if (!$this->isValidCurrency($currencyCode)) {
            Log::warning("Invalid currency code '{$currencyCode}', falling back to USD");
            $currencyCode = 'USD';
            $this->currency_iso = 'USD';
        }

        // Determine locale and get symbol
        $locale = $locale ?: app()->getLocale();
        $localeKey = $this->normalizeLocale($locale);

        $this->currency = self::$currencySymbols[$currencyCode][$localeKey];

        // Clear cached format when currency changes
        $this->clearFormatCache();

        return $this;
    }

    /**
     * Set the precision (decimal places for rounding)
     *
     * @param int $precision Number of decimal places (default: 2)
     * @return $this
     *
     * @example
     * ```php
     * $money->setPrecision(3); // 1500.123
     * $money->setPrecision(0); // 1500
     * ```
     */
    public function setPrecision(
        int $precision = 2
    ): static
    {
        $this->precision = max(0, $precision); // Ensure non-negative
        $this->clearFormatCache();
        return $this;
    }

    /**
     * Set the decimal places for the original amount
     *
     * @param int $decimal Number of decimal places (default: 2)
     * @return $this
     *
     * @example
     * ```php
     * $money->setDecimal(2); // Standard cents (100 = $1.00)
     * $money->setDecimal(3); // Thousandths (1000 = $1.000)
     * ```
     */
    public function setDecimal(
        int $decimal = 2
    ): static
    {
        $this->decimal = max(0, $decimal);
        $this->decimalMultiplier = (int) (10 ** $this->decimal); // Cache: 100, 1000, etc.
        return $this;
    }

    /**
     * Set the VAT/Tax percentage
     *
     * @param float|int|string $percentage Tax percentage (e.g., 15 for 15%)
     * @return $this
     * @throws InvalidArgumentException If percentage is not numeric
     *
     * @example
     * ```php
     * $money->setTax(15); // 15% VAT
     * $money->setTax(21.5); // 21.5% VAT
     * $money->setTax('5'); // 5% VAT (string accepted)
     * ```
     */
    public function setTax(
        float|int|string $percentage = 0
    ): static
    {
        $percentage = $this->sanitizeNumeric($percentage);

        $this->inc = true;
        $this->percentage = $percentage;
        $this->clearFormatCache();

        return $this;
    }

    // =========================================================================
    // AMOUNT SETTERS
    // =========================================================================

    /**
     * Set the amount directly without decimal conversion
     *
     * Used for row amounts that are already in the correct format
     *
     * @param float|int|string|null $amount The amount
     * @return $this
     * @throws InvalidArgumentException If amount is not numeric
     *
     * @example
     * ```php
     * $money->setRowAmount(150050); // Sets to 150050 directly
     * ```
     */
    public function setRowAmount(
        float|int|string|null $amount
    ): static
    {
        $amount = $this->sanitizeNumeric($amount ?? 0);
        $this->validateAmount($amount);

        $this->amount = $amount;
        $this->clearFormatCache();

        return $this;
    }

    /**
     * Set the amount with decimal conversion
     *
     * Main method for setting amounts. Divides by decimal multiplier
     * and rounds to specified precision.
     *
     * @param float|int|string|null $amount The amount to set
     * @return $this
     * @throws InvalidArgumentException If amount is not numeric
     *
     * @example
     * ```php
     * $money->setAmount(150000); // With 2 decimals: 1500.00
     * $money->setAmount('1500'); // String accepted: 1500.00
     * $money->setAmount(1500.50); // Float accepted: 1500.50
     * ```
     */
//    public function setAmount(float|int|string|null $amount): static
//    {
//        $amount = $this->sanitizeNumeric($amount ?? 0);
//        $normalizedAmount = round($amount / $this->getDecimal(), $this->precision);
//
//        $this->validateAmount($normalizedAmount);
//
//        $this->amount = $normalizedAmount;
//        $this->clearFormatCache();
//
//        return $this;
//    }

    /**
     * Set the amount with precision-aware normalization
     *
     * - Standard precision (2): Normalizes to whole cents (84338.10 → 84338)
     * - High precision (5+): Keeps fractional values (843.381 → 843.381)
     *
     * @param float|int|string|null $amount The amount to set
     * @return $this
     * @throws InvalidArgumentException If amount is not numeric
     *
     * @example
     * ```php
     * // Standard precision - normalizes
     * $money->setPrecision(2)->setDecimal(2)->setAmount(84338.10);
     * // Result: 84338 cents = €843.38
     *
     * // High precision - keeps fractional
     * $money->setPrecision(5)->setDecimal(5)->setAmount(843.381);
     * // Result: €0.00843 (with 5 decimals: €0.00843)
     *
     * // Or with matching decimal places
     * $money->setPrecision(5)->setDecimal(5)->setAmount(843381);
     * // Result: 843381 / 100000 = €8.43381
     * ```
     */
    public function setAmount(
        float|int|string|null $amount
    ): static
    {
        $amount = $this->sanitizeNumeric($amount ?? 0);

        // Only normalize to whole units for standard 2-decimal currency
        if ($this->shouldNormalize()) {
            // Round to whole cents to avoid fractional cents (84338.10 → 84338)
            $amount = round($amount);
        }

        $normalizedAmount = round($amount / $this->getDecimal(), $this->precision);

        $this->validateAmount($normalizedAmount);

        $this->amount = $normalizedAmount;
        $this->clearFormatCache();

        return $this;
    }

    // =========================================================================
    // AMOUNT GETTERS
    // =========================================================================

    /**
     * Get the amount as a float
     *
     * @param bool $inc Include VAT in the calculation
     * @param bool $onlyTax Return only the VAT amount
     * @return float The calculated amount
     *
     * @example
     * ```php
     * $money->setTax(15)->setAmount(1000);
     *
     * echo $money->getAmount(); // 1000.00
     * echo $money->getAmount(inc: true); // 1150.00
     * echo $money->getAmount(onlyTax: true); // 150.00
     * ```
     */
    public function getAmount(
        bool $inc = false,
        bool $onlyTax = false
    ): float
    {
        $amount = $this->amount;

        if ($this->inc && $inc) {
            $amount = $amount * (self::PERCENTAGE_BASE + $this->percentage) / self::PERCENTAGE_BASE;
        }

        if ($this->inc && $onlyTax) {
            $amount = $amount * $this->percentage / self::PERCENTAGE_BASE;
        }

        return $amount;
    }

    /**
     * Get the amount as an integer (row amount)
     *
     * Uses high precision factor for VAT calculations
     *
     * @param bool $inc Include VAT
     * @param bool $onlyTax Return only VAT
     * @return int The row amount
     *
     * @example
     * ```php
     * $money->setTax(15)->setAmount(1000);
     * echo $money->getRowAmount(); // 1000
     * echo $money->getRowAmount(inc: true); // 1150
     * ```
     */
    public function getRowAmount(
        bool $inc = false,
        bool $onlyTax = false
    ): int
    {
        $amount = $this->amount;

        if ($this->inc && $inc) {
            $amount = intval(
                round($amount * (self::PERCENTAGE_BASE + $this->percentage) / self::PRECISION_FACTOR) * 100
            );
        }

        if ($this->inc && $onlyTax) {
            $amount = $amount * $this->percentage / self::PERCENTAGE_BASE;
        }

        return (int) $amount;
    }

    /**
     * Get the amount without modifying the instance
     *
     * Safe method that doesn't mutate the object
     *
     * @param bool $inc Include VAT
     * @param bool $onlyTax Return only VAT
     * @return float The calculated amount
     *
     * @example
     * ```php
     * $base = $money->amount(); // Base amount
     * $withVat = $money->amount(inc: true); // With VAT
     * $vatOnly = $money->amount(onlyTax: true); // VAT only
     * ```
     */
    final public function amount(
        bool $inc = false,
        bool $onlyTax = false
    ): float
    {
        $amount = $this->amount;

        if ($this->inc && $inc) {
            $amount = $amount * (self::PERCENTAGE_BASE + $this->percentage) / self::PERCENTAGE_BASE;
        }

        if ($this->inc && $onlyTax) {
            $amount = $amount * $this->percentage / self::PERCENTAGE_BASE;
        }

        return $amount;
    }

    /**
     * Get the VAT/Tax amount
     *
     * @return float|int The tax amount
     *
     * @example
     * ```php
     * $money->setTax(15)->setAmount(1000);
     * echo $money->getTax(); // 150.00
     * ```
     */
    public function getTax(): float|int
    {
        return $this->amount * $this->percentage / self::PERCENTAGE_BASE;
    }

    // =========================================================================
    // ARITHMETIC OPERATIONS
    // =========================================================================

    /**
     * Add an amount to the current value
     *
     * @param int|float|string $money Amount to add
     * @return $this
     * @throws InvalidArgumentException If amount is not numeric
     *
     * @example
     * ```php
     * $money->setAmount(1000)->add(500); // 1500
     * $money->add('250.50'); // 1750.50
     * ```
     */
    public function add(
        int|float|string $money
    ): static
    {
        $money = $this->sanitizeNumeric($money);
        $this->amount = round($this->amount + $money, $this->precision);
        $this->clearFormatCache();

        return $this;
    }

    /**
     * Subtract an amount from the current value
     *
     * @param int|float|string $money Amount to subtract
     * @return $this
     * @throws InvalidArgumentException If amount is not numeric
     *
     * @example
     * ```php
     * $money->setAmount(1000)->subtract(200); // 800
     * $money->subtract('150.50'); // 649.50
     * ```
     */
    public function subtract(
        int|float|string $money
    ): static
    {
        $money = $this->sanitizeNumeric($money);
        $this->amount = round($this->amount - $money, $this->precision);
        $this->clearFormatCache();

        return $this;
    }

    /**
     * Multiply the current amount by a factor
     *
     * @param int|float|string $factor Factor to multiply by
     * @return $this
     * @throws InvalidArgumentException If factor is not numeric
     *
     * @example
     * ```php
     * $money->setAmount(100)->multiply(5); // 500
     * $money->multiply(1.5); // 750
     * ```
     */
    public function multiply(
        int|float|string $factor
    ): static
    {
        $factor = $this->sanitizeNumeric($factor);
        $this->amount = round($this->amount * $factor, $this->precision);
        $this->clearFormatCache();

        return $this;
    }

    /**
     * Divide the current amount by a divisor
     *
     * @param int|float|string $divisor Number to divide by
     * @return $this
     * @throws InvalidArgumentException If divisor is not numeric
     * @throws RuntimeException If divisor is zero or near-zero
     *
     * @example
     * ```php
     * $money->setAmount(1000)->divide(4); // 250
     * $money->divide(2.5); // 100
     * ```
     */
    public function divide(
        int|float|string $divisor
    ): static
    {
        $divisor = $this->sanitizeNumeric($divisor);

        if (abs($divisor) < PHP_FLOAT_EPSILON) {
            throw new RuntimeException("Division by zero or near-zero value");
        }

        $this->amount = round($this->amount / $divisor, $this->precision);
        $this->clearFormatCache();

        return $this;
    }

    // =========================================================================
    // COMPARISON METHODS
    // =========================================================================

    /**
     * Check if this amount is greater than another
     *
     * @param Moneys $other The money instance to compare with
     * @return bool True if this is greater
     *
     * @example
     * ```php
     * if ($price->isGreaterThan($budget)) {
     *     echo "Over budget!";
     * }
     * ```
     */
    public function isGreaterThan(
        Moneys $other
    ): bool
    {
        return $this->amount > $other->amount;
    }

    /**
     * Check if this amount is less than another
     *
     * @param Moneys $other The money instance to compare with
     * @return bool True if this is less
     *
     * @example
     * ```php
     * if ($balance->isLessThan($minimumBalance)) {
     *     echo "Low balance!";
     * }
     * ```
     */
    public function isLessThan(
        Moneys $other
    ): bool
    {
        return $this->amount < $other->amount;
    }

    /**
     * Check if this amount equals another
     *
     * Uses epsilon comparison for floating point safety
     *
     * @param Moneys $other The money instance to compare with
     * @return bool True if equal
     *
     * @example
     * ```php
     * if ($payment->equals($invoice)) {
     *     echo "Fully paid!";
     * }
     * ```
     */
    public function equals(
        Moneys $other
    ): bool
    {
        return abs($this->amount - $other->amount) < PHP_FLOAT_EPSILON;
    }

    /**
     * Check if the amount is zero
     *
     * @return bool True if zero or near-zero
     *
     * @example
     * ```php
     * if ($balance->isZero()) {
     *     echo "Account is empty";
     * }
     * ```
     */
    public function isZero(): bool
    {
        return abs($this->amount) < PHP_FLOAT_EPSILON;
    }

    /**
     * Check if the amount is positive
     *
     * @return bool True if greater than zero
     *
     * @example
     * ```php
     * if ($profit->isPositive()) {
     *     echo "Making money!";
     * }
     * ```
     */
    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Check if the amount is negative
     *
     * @return bool True if less than zero
     *
     * @example
     * ```php
     * if ($balance->isNegative()) {
     *     echo "Overdrawn!";
     * }
     * ```
     */
    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    // =========================================================================
    // FORMATTING METHODS
    // =========================================================================

    /**
     * Format the amount with currency symbol and proper positioning
     *
     * Main formatting method with caching support
     *
     * @param bool $inc Include VAT in formatted amount
     * @param bool $onlyTax Format only the VAT amount
     * @return string Formatted currency string
     *
     * @example
     * ```php
     * // English locale
     * $money->setAmount(1500)->format(); // "USD 1,500.00"
     *
     * // Arabic locale
     * $money->setCurrency('SAR')->format(); // "1,500.00 ﷼"
     *
     * // With VAT
     * $money->setTax(15)->format(inc: true); // "USD 1,725.00"
     *
     * // VAT only
     * $money->setTax(15)->format(inc: false, onlyTax: true); // "USD 225.00"
     * ```
     */
    final public function format(
        bool $inc = false,
        bool $onlyTax = false
    ): string
    {
        // Check cache
        $cacheKey = "{$inc}_{$onlyTax}_{$this->amount}_{$this->currency}_{$this->percentage}";

        if ($this->cachedFormat !== null && $this->lastCacheKey === $cacheKey) {
            return $this->cachedFormat;
        }

        $amount = $this->amount;

        if ($this->inc && $inc) {
            $amount = $amount * (self::PERCENTAGE_BASE + $this->percentage) / self::PERCENTAGE_BASE;
        }

        if ($this->inc && $onlyTax) {
            $amount = $amount * $this->percentage / self::PERCENTAGE_BASE;
        }

        $this->cachedFormat = $this->formatCurrency($amount);
        $this->lastCacheKey = $cacheKey;

        return $this->cachedFormat;
    }

    /**
     * Format row amount with VAT calculations
     *
     * Uses high precision factor for accurate VAT
     *
     * @param bool $inc Include VAT
     * @param bool $onlyTax Format only VAT
     * @return string Formatted currency string
     *
     * @example
     * ```php
     * $money->setTax(15)->setAmount(1000);
     * echo $money->rowFormat(); // "USD 1,000.00"
     * echo $money->rowFormat(inc: true); // "USD 1,150.00"
     * ```
     */
    final public function rowFormat(
        bool $inc = false,
        bool $onlyTax = false
    ): string
    {
        $amount = $this->amount;

        if ($this->inc && $inc) {
            $amount = intval(
                    round($amount * (self::PERCENTAGE_BASE + $this->percentage) / self::PRECISION_FACTOR) * 100
                ) / $this->getDecimal();
        }

        if ($this->inc && $onlyTax) {
            $amount = $amount * $this->percentage / self::PERCENTAGE_BASE;
        }

        return $this->formatCurrency($amount);
    }

    // =========================================================================
    // SPECIAL METHODS
    // =========================================================================

    /**
     * Create a new instance containing only the VAT amount
     *
     * @param int|float|string $percentage VAT percentage
     * @return Moneys New instance with VAT amount
     *
     * @example
     * ```php
     * $base = moneys()->setAmount(1000);
     * $vat = $base->newFromTax(15);
     * echo $vat->format(); // "USD 150.00"
     * ```
     */
    public function newFromTax(
        int|float|string $percentage
    ): Moneys
    {
        $cloned = clone $this;
        $cloned->setTax($percentage);
        return $cloned->setAmount($cloned->getAmount(onlyTax: true));
    }

    /**
     * Calculate the net amount excluding VAT
     *
     * Uses high precision calculations
     *
     * @return $this
     *
     * @example
     * ```php
     * $gross = moneys()->setAmount(1150)->setTax(15);
     * $net = $gross->calculateExVat();
     * echo $net->format(); // "USD 1,000.00"
     * ```
     */
    public function calculateExVat(): static
    {
        $grossAmountCents = intval(round($this->amount));
        $netAmountLargeInt = intval(
            round($grossAmountCents * self::PRECISION_FACTOR / (self::PERCENTAGE_BASE + $this->percentage))
        );

        $this->amount = $netAmountLargeInt;
        $this->clearFormatCache();

        return $this;
    }

    /**
     * Generate a receipt with subtotal, VAT, and total
     *
     * @param bool $formatted Return formatted strings or raw numbers
     * @return Collection Collection with 'subtotal', 'vat', 'total'
     *
     * @example
     * ```php
     * $money = moneys()->setTax(15)->setAmount(1000);
     * $receipt = $money->receipt();
     * // [
     * //     'subtotal' => 'USD 1,000.00',
     * //     'vat' => 'USD 150.00',
     * //     'total' => 'USD 1,150.00'
     * // ]
     *
     * $receiptRaw = $money->receipt(formatted: false);
     * // [
     * //     'subtotal' => 1000.00,
     * //     'vat' => 150.00,
     * //     'total' => 1150.00
     * // ]
     * ```
     */
    public function receipt(
        bool $formatted = true
    ): Collection
    {
        $amount = $this->amount;
        $tax = $this->numberFormat($this->getTax());
        $total = $this->numberFormat($this->amount + $this->getTax());

        if ($formatted) {
            $amount = $this->formatCurrency($this->amount);
            $tax = $this->formatCurrency($this->getTax());
            $total = $this->formatCurrency($this->amount + $this->getTax());
        }

        return collect([
            'subtotal' => $amount,
            'vat' => $tax,
            'total' => $total,
        ]);
    }

    // =========================================================================
    // IMMUTABLE METHODS (Return new instance)
    // =========================================================================

    /**
     * Create a new instance with a different amount
     *
     * @param float|int|string $amount New amount
     * @return static New instance
     *
     * @example
     * ```php
     * $price1 = moneys()->setAmount(1000);
     * $price2 = $price1->withAmount(2000);
     * // $price1 is still 1000, $price2 is 2000
     * ```
     */
    public function withAmount(
        float|int|string $amount
    ): static
    {
        $new = clone $this;
        return $new->setAmount($amount);
    }

    /**
     * Create a new instance with a different currency
     *
     * @param string $currency Currency code
     * @param string|null $locale Locale for symbol
     * @return static New instance
     *
     * @example
     * ```php
     * $usd = moneys()->setAmount(1000);
     * $eur = $usd->withCurrency('EUR');
     * ```
     */
    public function withCurrency(
        string $currency,
        ?string $locale = null
    ): static
    {
        $new = clone $this;
        return $new->setCurrency($currency, $locale);
    }

    /**
     * Create a new instance with a different tax rate
     *
     * @param float|int $percentage Tax percentage
     * @return static New instance
     *
     * @example
     * ```php
     * $noTax = moneys()->setAmount(1000);
     * $withTax = $noTax->withTax(15);
     * ```
     */
    public function withTax(
        float|int $percentage
    ): static
    {
        $new = clone $this;
        return $new->setTax($percentage);
    }

    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    /**
     * Get currency information
     *
     * @return array Array with currency details
     *
     * @example
     * ```php
     * $info = $money->toArray();
     * // [
     * //     'amount' => 1500.00,
     * //     'formatted' => 'USD 1,500.00',
     * //     'currency' => '$',
     * //     'currency_iso' => 'USD',
     * //     'precision' => 2,
     * //     'decimal' => 2,
     * //     'with_tax' => false,
     * //     'tax_percentage' => 0
     * // ]
     * ```
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'formatted' => $this->format(),
            'currency' => $this->currency,
            'currency_iso' => $this->currency_iso,
            'precision' => $this->precision,
            'decimal' => $this->decimal,
            'with_tax' => $this->inc,
            'tax_percentage' => $this->percentage,
        ];
    }

    /**
     * Debug info for var_dump()
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    /**
     * Get the currency symbol
     *
     * @return string Currency symbol
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Get the ISO currency code
     *
     * @return string ISO code (e.g., 'USD', 'EUR')
     */
    public function getCurrencyIso(): string
    {
        return $this->currency_iso;
    }

    /**
     * Convert to string (formatted)
     *
     * @return string Formatted currency string
     *
     * @example
     * ```php
     * $money = moneys()->setAmount(1500);
     * echo $money; // "USD 1,500.00"
     * echo (string) $money; // "USD 1,500.00"
     * ```
     */
    public function __toString(): string
    {
        return $this->format();
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Get decimal multiplier (cached)
     *
     * @return int Multiplier (e.g., 100 for 2 decimals)
     */
    private function getDecimal(): int
    {
        return $this->decimalMultiplier;
    }

    /**
     * Format currency with proper positioning based on locale
     *
     * @param float $amount Amount to format
     * @return string Formatted string with currency
     */
    private function formatCurrency(
        float $amount
    ): string
    {
        $formatted = number_format($amount, $this->precision, ',', '.');
        $locale = app()->getLocale();

        // Arabic: number then symbol (e.g., "1,234.56 د.إ")
        if ($this->isArabicLocale($locale)) {
            return "{$formatted} {$this->currency}";
        }

        // Others: symbol then number (e.g., "USD 1,234.56")
        return "{$this->currency} {$formatted}";
    }

    /**
     * Normalize locale to 'en' or 'ar'
     *
     * Supported locales: en, de, nl, be, fr → 'en'
     *                    ar, ara → 'ar'
     *
     * @param string $locale Locale code
     * @return string Normalized locale ('en' or 'ar')
     */
    private function normalizeLocale(
        string $locale
    ): string
    {
        return $this->isArabicLocale($locale) ? 'ar' : 'en';
    }

    /**
     * Static version of normalizeLocale
     *
     * @param string $locale Locale code
     * @return string Normalized locale
     */
    private static function normalizeLocaleStatic(
        string $locale
    ): string
    {
        $locale = strtolower($locale);
        return str_starts_with($locale, 'ar') ? 'ar' : 'en';
    }

    /**
     * Check if locale is Arabic
     *
     * @param string $locale Locale code
     * @return bool True if Arabic locale
     */
    private function isArabicLocale(
        string $locale
    ): bool
    {
        $locale = strtolower($locale);
        return str_starts_with($locale, 'ar');
    }

    /**
     * Validate currency code
     *
     * @param string $code Currency code
     * @return bool True if valid
     */
    private function isValidCurrency(
        string $code
    ): bool
    {
        return isset(self::$currencySymbols[$code]);
    }

    /**
     * Sanitize and validate numeric input
     *
     * @param mixed $value Value to sanitize
     * @return float|int Sanitized numeric value
     * @throws InvalidArgumentException If not numeric
     */
    private function sanitizeNumeric(
        mixed $value
    ): float|int
    {
        if (is_string($value)) {
            $value = str_replace(',', '', $value);
        }

        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                "Invalid numeric value provided: '" . var_export($value, true) .
                "'. Expected a number, got " . gettype($value)
            );
        }

        return $value;
    }

    /**
     * Validate amount is within allowed range
     *
     * @param float $amount Amount to validate
     * @throws InvalidArgumentException If out of range
     */
    private function validateAmount(
        float $amount
    ): void
    {
        if ($amount > self::MAX_AMOUNT || $amount < self::MIN_AMOUNT) {
            throw new InvalidArgumentException(
                "Amount {$amount} exceeds allowed range [" . self::MIN_AMOUNT . ", " . self::MAX_AMOUNT . "]"
            );
        }
    }

    /**
     * Format a number with precision
     *
     * @param float|int|string $amount Amount to format
     * @return float Formatted number
     */
    private function numberFormat(
        float|int|string $amount
    ): float
    {
        $amount = $this->sanitizeNumeric($amount);
        return round($amount, $this->precision);
    }

    /**
     * Clear the format cache
     */
    private function clearFormatCache(): void
    {
        $this->cachedFormat = null;
        $this->lastCacheKey = null;
    }

    // =========================================================================
    // BULK OPERATIONS (Static)
    // =========================================================================

    /**
     * Sum multiple Moneys instances
     *
     * @param array<Moneys> $moneys Array of Moneys instances
     * @return static Sum of all amounts
     *
     * @example
     * ```php
     * $total = Moneys::sum([$price1, $price2, $price3]);
     * ```
     */
    public static function sum(
        array $moneys
    ): static
    {
        if (empty($moneys)) {
            return static::zero();
        }

        $result = clone $moneys[0];

        for ($i = 1; $i < count($moneys); $i++) {
            $result->add($moneys[$i]->amount);
        }

        return $result;
    }

    /**
     * Calculate average of multiple Moneys instances
     *
     * @param array<Moneys> $moneys Array of Moneys instances
     * @return static Average amount
     *
     * @example
     * ```php
     * $average = Moneys::average([$price1, $price2, $price3]);
     * ```
     */
    public static function average(
        array $moneys
    ): static
    {
        if (empty($moneys)) {
            return static::zero();
        }

        $sum = static::sum($moneys);
        return $sum->divide(count($moneys));
    }

    /**
     * Quick format a price with flexible precision
     *
     * @param float|int|string|null $amount Amount in smallest unit (cents for precision=2)
     * @param string|null $currency Currency code
     * @param int $precision Decimal precision (2, 5, 10, etc.)
     * @return string Formatted price
     *
     * @example
     * ```php
     * // Standard currency (2 decimals)
     * echo Moneys::display(84338); // "€ 843.38"
     *
     * // High precision for per-piece (5 decimals)
     * echo Moneys::display(843381, precision: 5); // "€ 8.43381"
     *
     * // Ultra precision (10 decimals)
     * echo Moneys::display(8433810, precision: 10); // "€ 0.8433810000"
     * ```
     */
    public static function display(
        float|int|string|null $amount,
        ?string $currency = null,
        int $precision = self::PRECISION_STANDARD
    ): string
    {
        return (new static())
            ->setPrecision($precision)
            ->setDecimalForPrecision($precision)
            ->setCurrency($currency)
            ->setAmount($amount ?? 0)
            ->format();
    }

    /**
     * Quick format with VAT and flexible precision
     *
     * @param float|int|string|null $amount Amount
     * @param int|float $vat VAT percentage
     * @param bool $includeVat Include VAT in result
     * @param string|null $currency Currency code
     * @param int $precision Decimal precision
     * @return string Formatted price
     *
     * @example
     * ```php
     * echo Moneys::displayWithVat(100000, 21); // "€ 1,000.00"
     * echo Moneys::displayWithVat(100000, 21, true); // "€ 1,210.00"
     * echo Moneys::displayWithVat(10000, 21, precision: 5); // "€ 0.10000"
     * ```
     */
    public static function displayWithVat(
        float|int|string|null $amount,
        int|float $vat,
        bool $includeVat = false,
        ?string $currency = null,
        int $precision = self::PRECISION_STANDARD
    ): string
    {
        return (new static())
            ->setPrecision($precision)
            ->setDecimalForPrecision($precision)
            ->setCurrency($currency)
            ->setTax($vat)
            ->setAmount($amount ?? 0)
            ->format(inc: $includeVat);
    }

    /**
     * Quick format VAT amount with precision
     *
     * @param float|int|string|null $amount Base amount
     * @param int|float $vat VAT percentage
     * @param string|null $currency Currency code
     * @param int $precision Decimal precision
     * @return string Formatted VAT amount
     */
    public static function displayVat(
        float|int|string|null $amount,
        int|float $vat,
        ?string $currency = null,
        int $precision = self::PRECISION_STANDARD
    ): string
    {
        return (new static())
            ->setPrecision($precision)
            ->setDecimalForPrecision($precision)
            ->setCurrency($currency)
            ->setTax($vat)
            ->setAmount($amount ?? 0)
            ->format(inc: false, onlyTax: true);
    }

    /**
     * Quick calculate with flexible precision
     *
     * @param float|int|string|null $amount Amount
     * @param int|float $vat VAT percentage
     * @param bool $includeVat Include VAT
     * @param bool $onlyVat Return only VAT
     * @param int $precision Calculation precision
     * @return float Calculated amount
     */
    public static function calculate(
        float|int|string|null $amount,
        int|float $vat = 0,
        bool $includeVat = false,
        bool $onlyVat = false,
        int $precision = self::PRECISION_STANDARD
    ): float
    {
        $instance = (new static())
            ->setPrecision($precision)
            ->setDecimalForPrecision($precision)
            ->setAmount($amount ?? 0);

        if ($vat > 0) {
            $instance->setTax($vat);
        }

        return $instance->amount(inc: $includeVat, onlyTax: $onlyVat);
    }

    // =========================================================================
    // PRECISION MANAGEMENT
    // =========================================================================

    /**
     * Set decimal places based on precision
     *
     * Automatically adjusts decimal multiplier for different precision levels:
     * - Precision 2 (standard): 100 (cents)
     * - Precision 5 (high): 100000 (0.00001 units)
     * - Precision 10 (ultra): 10000000000 (0.0000000001 units)
     *
     * @param int $precision Decimal precision
     * @return $this
     */
    public function setDecimalForPrecision(int $precision): static
    {
        // For standard currency (2 decimals), use 2 decimal places
        // For higher precision, match the precision
        $this->setDecimal(max(2, $precision));
        return $this;
    }

    /**
     * Check if normalization should be applied
     *
     * Only normalize to whole units when using standard 2-decimal currency
     * Keep fractional values for high-precision calculations
     *
     * @return bool True if should normalize
     */
    private function shouldNormalize(): bool
    {
        // Only normalize for standard 2-decimal currency
        return $this->precision === self::PRECISION_STANDARD && $this->decimal === 2;
    }

    // =========================================================================
    // UPDATED AMOUNT SETTER - PRECISION AWARE
    // =========================================================================



    /**
     * Set amount without any normalization (raw value)
     *
     * Use this when you already have a properly formatted decimal value
     *
     * @param float|int|string|null $amount Raw decimal amount
     * @return $this
     *
     * @example
     * ```php
     * $money->setRawAmount(843.381); // Sets exactly 843.381
     * echo $money->format(); // "€ 843.38100" (with precision 5)
     * ```
     */
    public function setRawAmount(float|int|string|null $amount): static
    {
        $amount = $this->sanitizeNumeric($amount ?? 0);
        $this->validateAmount($amount);

        $this->amount = $amount;
        $this->clearFormatCache();

        return $this;
    }



    /**
     * @param array $prices
     * @param int $quantity
     * @param string $currentTenant
     * @return array
     * @throws GuzzleException
     */
    public static function applyMyMargin(
        array &$price,
        array $margin
    ): array
    {

        // Pre-calculate margin type and value
        $isFixed = $margin['type'] === 'fixed';
        $marginValue = $margin['value'];
        $marginRatio = $isFixed ? 0 : $marginValue / 100;

        $qty = $price['qty'];

        // Calculate margin profit
        $profit = $isFixed
            ? $marginValue
            : $price['selling_price_ex'] * $marginRatio;

        // Apply margin to selling price
        $price['selling_price_ex'] += $profit;

        // Update price and gross_price to match selling_price_ex
        $price['p'] = $price['selling_price_ex'];
        $price['gross_price'] = $price['selling_price_ex'];

        // Calculate selling price with VAT
        $vatRate = (float) $price['vat'];
        $price['selling_price_inc'] = $price['selling_price_ex'] * (1 + $vatRate / 100);

        // Calculate price per piece
        $price['gross_ppp'] = $qty > 0 ? $price['selling_price_ex'] / $qty : 0;
        $price['ppp'] = $qty > 0 ? $price['selling_price_inc'] / $qty : 0;

        // Set profit
        $price['profit'] = $profit;
        $price['margins'] = $margin;

        return $price;
    }

    /**
     * @param int $quantity
     * @param string $currentTenant
     * @return array
     * @throws GuzzleException
     */
    public static function getMargin(
        int $quantity,
        string $currentTenant
    ): array
    {
        // Skip collect() overhead - direct array access
        $marginData = (new MarginService)->obtainMargin($currentTenant)[0] ?? null;

        // Early validation
        if ((!$quantity && $quantity !== 0) || !$marginData || empty($marginData['slots'])) {
            return [];
        }

        $slots = $marginData['slots'];

        // Type check once instead of during iteration
        if (!is_array($slots)) {
            return [];
        }

        // Find matching slot with early return
        foreach ($slots as $slot) {
            $from = (int) ($slot['from'] ?? 0);
            $to = (int) ($slot['to'] ?? -1);

            // Simplified range check with early return
            if ($quantity >= $from && ($to === -1 || $quantity <= $to)) {
                return [
                    'value' => $slot['value'] ?? 0,
                    'type' => $slot['type'] ?? 0,
                ];
            }
        }

        return [];
    }

}

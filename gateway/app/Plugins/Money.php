<?php
namespace App\Plugins;


use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Parser\IntlMoneyParser;
use Money\Money as BaseMoney;
use NumberFormatter;

/**
 * @deprecated
 */
class Money
{
    /**
     * @var BaseMoney
     */
    private BaseMoney $money;

    /**
     * get this value from settings
     * @var string
     */
    private string $currency = 'EUR';

    /**
     * get local from session or settings
     * @var string
     */
    private string $localStyle = 'nl-NL';

    /**
     * Money constructor.
     * @param int|null $value
     */
    public function __construct(
        ?int $value
    )
    {
        $this->money = new BaseMoney($value, new Currency(
            $this->currency
        ));
    }

    /**
     * get price as integer
     * @return int
     */
    final public function amount(): int
    {
        return $this->money->getAmount();
    }

    /**
     * @param int $decimals
     * @param bool $includeSymbol
     * @return string
     */
    final public function formatted(
        bool $includeSymbol = true,
        int $decimals = 2
    ): string
    {
        $formatter = new NumberFormatter(
            $this->localStyle,
            $includeSymbol ? NumberFormatter::CURRENCY : NumberFormatter::DECIMAL
        );

        // Ensure the formatter always includes 2 decimals
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

        $intlFormatter = new IntlMoneyFormatter($formatter, new ISOCurrencies());

        return $intlFormatter->format($this->money);
    }

    /**
     * Calculate the VAT amount based on a given percentage.
     * @param float $vatPercentage
     * @return Money
     */
    public function calcVat(float $vatPercentage): Money
    {
        $vatAmount = $this->money->multiply($vatPercentage / 100);
        return new Money($vatAmount->getAmount());
    }

    /**
     * https://stackoverflow.com/questions/5139793/unformat-money-when-parsing-in-php#19764699
     * Formats any formatte currency string to a Money instance:
     *  '€ 21,00' => 2100,
     *  '21.00 £' => 2100,
     *  '1.234,567' => 123457, // Rounds to 1234.57
     *  '1,234.567' => 123457, // Rounds to 1234.57
     *  '1.234,56789' => 123457, // Rounds to 1234.57
     *  '1.234' => 123400,
     *  '1,234' => 123400,
     *  '1.234,5' => 123450,
     *  '1.234,567890' => 123457, // Rounds to 1234.57
     *  '€ 1.234.567,89' => 123456789,
     *  '$ 1,234,567.89' => 123456789
     * @param string $money
     * @return Money
     */
    public static function fromString(string $money): Money
    {
        // Remove all non-numeric characters except . and ,
        $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);

        // Count all numbers
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);

        // Split the string into parts before and after the decimal separator
        $parts = preg_split('/[,\.]/', $cleanString);

        if (count($parts) === 1) {
            // No decimal part
            return new self((int)$parts[0] * 100);
        }

        // Get the last part as decimal and all other parts joined as the integer part
        $decimalPart = end($parts);
        $integerPart = implode('', array_slice($parts, 0, -1));

        // Handle the decimal part appropriately
        if (strlen($decimalPart) > 2) {
            // For more than 2 decimal places, round to nearest cent
            $decimalValue = round($decimalPart / pow(10, strlen($decimalPart) - 2));
        } else {
            // Pad with zeros if less than 2 decimal places
            $decimalValue = str_pad($decimalPart, 2, '0', STR_PAD_RIGHT);
        }

        // Combine integer and decimal parts
        $finalValue = $integerPart . str_pad($decimalValue, 2, '0', STR_PAD_RIGHT);

        return new self((int)$finalValue);
    }

    /**
     * Get the currency symbol for the current currency
     * @return string
     */
    public function currencySymbol(): string
    {
        $formatter = new NumberFormatter($this->localStyle, NumberFormatter::CURRENCY);
        return $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
    }

    /**
     * Subtract another Money object from this one
     * @param Money $other
     * @return Money
     */
    public function subtract(Money $other): Money
    {
        $result = $this->money->subtract($other->money);
        return new Money($result->getAmount());
    }

    /**
     * Add another Money object to this one
     * @param Money $other
     * @return Money
     */
    public function add(Money $other): Money
    {
        $result = $this->money->add($other->money);
        return new Money($result->getAmount());
    }

    /**
     * Multiply this Money object by a given multiplier
     * @param $multiplier
     * @return Money
     */
    public function multiply($multiplier): Money
    {
        $result = $this->money->multiply($multiplier);
        return new Money($result->getAmount());
    }

    /**
     * Implement the divide method from the Money class
     * @param $divisor
     * @return Money
     */
    public function divide($divisor): Money
    {
        $result = $this->money->divide($divisor);
        return new Money($result->getAmount());
    }

    /**
     * Compare if this Money object is greater than another Money object
     * @param Money $other
     * @return bool
     */
    public function greaterThan(Money $other): bool
    {
        return $this->money->greaterThan($other->money);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->format();
    }
}

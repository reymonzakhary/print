<?php


namespace App\Contracts\Orders;

use App\Foundation\Settings\Settings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InvoiceNumberContract
{
    protected Model $model;

    /**
     * @param string $method
     * @param mixed  $arguments
     * @return mixed
     */
    public function __invoke(
        string $method,
        array  $arguments = []
    )
    {
        return $this->{$method}($arguments);
    }

    /**
     * @param array $arguments
     * @return int
     */
    private function year4Digit(
        array $arguments
    ): int
    {
        return (int)Carbon::now()->format('Y');
    }

    /**
     * @param Model $model
     * @return string[]
     */
    public function getPattern(
        Model $model
    ): array
    {
        $this->model = $model;
        $allowIncrement = !empty(Settings::invoiceNumberPattern());
        return $allowIncrement ? array_merge(explode(',', Settings::invoiceNumberPattern()), ['id']) : ['id'];
    }

    /**
     * Generates an invoice number pattern based on the given arguments.
     *
     * @param array $arguments The arguments for generating the invoice number pattern.
     * @return string|null The generated invoice number pattern.
     */
    private function pattern(
        array $arguments
    ): ?string
    {
        // restart order number
        // check if you have orders in this year? yes with invoice ? yes
        // what is the last order this year? the id is 33 and the invoice number is ? {year}-{id} + 2022-22-00222
        // okay good! then we can test it as array and check if the array have more then one key? yes it have.
        // what is the style of my last order this year?
        // return result
        // 100
        // 2300 ------ 1
        // {year}-100 -----2
        // 2300 -40
        //// 100  --- 40000000
        /// {2012} - 100
        // 2013-100
        // 4001
        // else we dont have orders this year good then we gone check the style from settings.
        $invoiceNrRule = Settings::invoiceNumberIdLength();

        $max = $this->model->whereNotNull('invoice_nr')->orderBy('invoice_date', 'DESC');
        if (Settings::restartInvoiceId()) {
            $max->whereYear('invoice_date', Carbon::now()->year);
        }

        $max = $max->latest()->skip(0)->value('invoice_nr');
        $last_num = (int)current(array_reverse(explode('-', $max))) ?? Settings::invoiceStartNumber();
        if (!$max) {
            $res = str_pad(Settings::invoiceStartNumber(), $invoiceNrRule, "0", STR_PAD_LEFT);
        } else {
            $res = str_pad($last_num + 1, $invoiceNrRule, "0", STR_PAD_LEFT);
        }
        return $res;

    }

    /**
     * Get the pattern using the specified arguments.
     *
     * @param array $arguments The arguments for generating the pattern.
     * @return string|null The generated pattern.
     */
    private function id(
        array $arguments
    ): ?string
    {
        return $this->pattern($arguments);
    }


    /**
     * Convert the year to a two-digit format.
     *
     * @param array $arguments The array of arguments.
     * @return int The two-digit year.
     */
    private function year2Digit(
        array $arguments
    ): int
    {
        return (int)substr(Carbon::now()->format('Y'), 2, 2);
    }

    /**
     * Get the current month number.
     *
     * @param array $arguments The array of arguments.
     * @return int The month number.
     */
    private function monthNumber(
        array $arguments
    ): int
    {
        return (int)Carbon::now()->format('m');
    }

    /**
     * Get the customer ID from the given arguments.
     *
     * @param array $arguments The array of arguments.
     * @return int The customer ID, or null if it is not present.
     */
    private function customerId(
        array $arguments
    ): int
    {
        return optional($arguments)['user_id'];
    }

}

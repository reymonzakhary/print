<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Contracts\Orders\InvoiceNumberContract;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

trait HasInvoiceNumber
{
    /**
     * Boot method for the model
     */
    public static function boot(): void
    {
        parent::boot();

        static::creating(function (Model $model) {
            self::generateInvoiceData($model);
        });

        static::updating(function (Model $model): void {
            self::generateInvoiceData($model);
        });
    }

    /**
     * @param Model $model
     *
     * @return void
     */
    private static function generateInvoiceData(Model $model): void
    {
        if (!$model->getAttribute('invoice_nr') && $model->getAttribute('st') !== Status::DRAFT->value) {
            $model->setAttribute('invoice_nr', self::generateInvoiceNumber($model));
            $model->setAttribute('invoice_date', Carbon::now());
        }
    }

    /**
     * Generate the invoice number template for a given order.
     *
     * @param Model $model
     *
     * @return string The generated invoice number template.
     */
    private static function generateInvoiceNumber(Model $model): string
    {
        $template = new InvoiceNumberContract();

        return collect($template->getPattern($model))
            ->map(function (string $mode) use ($template, $model) {
                $method = Str::camel(Str::replaceArray('-', [''], $mode));

                return $template($method, $model->toArray());
            })
            ->implode('-');
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Foundation\Settings\Settings;
use App\Foundation\Status\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait HasOrderNumber
{
    /**
     * Boot method for the model
     */
    public static function boot(): void
    {
        parent::boot();

        static::updating(function (Model $model): void {
            if ($model->getAttribute('type') === true) {
                if ($model->getAttribute('st') === Status::NEW && !$model->getAttribute('order_nr')) {
                    $model->setAttribute('order_nr', self::generateOrderNumber($model));
                }
            }
        });
    }

    /**
     * Generate a new order number.
     *
     * @return string The new generated order number
     */
    private static function generateOrderNumber(Model $model): string
    {
        $orderNumberPaddingLength = (int)Settings::orderNumberPattern();

        $highestOrderNumberStoredInTheDatabase = $model->withTrashed()
            ->whereNotNull('order_nr')
            ->orderBy('order_nr', 'DESC')
            ->value('order_nr');

        $highestOrderNumberInRawFormat = !empty($highestOrderNumberStoredInTheDatabase) ?
            ((int)$highestOrderNumberStoredInTheDatabase + 1) : (int)Settings::orderStartNumber();

        while (self::isOrderNumberExist(
            $model,
            $formattedOrderNumber = self::doFormatRawOrderNumber(
                $highestOrderNumberInRawFormat,
                $orderNumberPaddingLength
            )
        )) {
            Log::warning(
                sprintf(
                    'Order-Number-Generator has generated a value "%s" that already exists in the database. Will keep incrementing until find a unique one',
                    $formattedOrderNumber,
                ),
                [
                    'model' => __CLASS__
                ]
            );

            $highestOrderNumberInRawFormat++;
        }

        return $formattedOrderNumber;
    }

    /**
     * Do formatting for the order number from raw format to the DB format
     *
     * @param int $orderNumberInRawFormat
     * @param int $numberPaddingLength
     *
     * @return string
     */
    private static function doFormatRawOrderNumber(
        int $orderNumberInRawFormat,
        int $numberPaddingLength
    ): string {
        return str_pad((string)$orderNumberInRawFormat, $numberPaddingLength, "0", STR_PAD_LEFT);
    }

    /**
     * Check if the order number already exist in the database
     *
     * @param Model $model
     * @param string $orderNumber
     *
     * @return bool
     */
    private static function isOrderNumberExist(Model $model, string $orderNumber): bool
    {
        return $model->withTrashed()->where('order_nr', $orderNumber)->count() > 0;
    }
}

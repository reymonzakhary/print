<?php


namespace App\Models\Traits;

use App\Actions\PriceAction\CalculationAction;
use App\Plugins\Moneys;

trait HasPrice
{
    /**
     * @param int|null $value
     * @return Moneys
     */
    final public function getPriceAttribute(
        ?int $value
    ): Moneys
    {
        return (new Moneys())->setAmount($value);
    }

    /**
     * @return string
     */
    final public function getFormattedPriceAttribute(): string
    {
        return $this->price->format();
    }

    final public function getFormattedVatAttribute(): string
    {
        $vats = (new CalculationAction($this))->getVats();
        return moneys()->setAmount($vats)->format();
    }
}

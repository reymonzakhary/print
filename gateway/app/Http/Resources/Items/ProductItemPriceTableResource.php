<?php

namespace App\Http\Resources\Items;

use App\Plugins\Moneys;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductItemPriceTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $moneys = (new Moneys())->setPrecision(2)
            ->setDecimal(2);

        return [
            'pm' => optional($this)['pm'],
            'dlv' => optional($this)['dlv'],
            'qty' => optional($this)['qty'],
            'supplier_product' => optional($this)['supplier_product'],
            'p' => optional($this)['p'],
            'display_p' => $moneys->setAmount(optional($this)['p'])->format(),
            'ppp' => optional($this)['ppp'],
            'display_ppp' => $moneys->setAmount(optional($this)['ppp'])->format(),
            'resale_p' => optional($this)['resale_p'],
            'resale_ppp' => (int)optional($this)['resale_p'] ? (int)optional($this)['resale_p'] / (int)$this['qty'] : 0,
            'display_resale_p' => $moneys->setAmount(optional($this)['resale_p'])->format() ,
            'display_resale_ppp' => (int)optional($this)['resale_p'] ?
                $moneys->setAmount(optional($this)['resale_p'])->divide((int)$this['qty'])->format():
                $moneys->setAmount(0)->format()
        ];
    }
}

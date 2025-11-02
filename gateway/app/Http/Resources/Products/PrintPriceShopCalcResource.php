<?php

namespace App\Http\Resources\Products;

use App\Plugins\Moneys;
use App\Plugins\PriceFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintPriceShopCalcResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        // @TODO check the response before removing the comments this is a new helper of the prices
        return PriceFormatter::format($this->resource);

//        return [
//            'id' => optional($this->resource)['id'],
//            'pm' => optional($this->resource)['pm']??'',
//            'qty' => optional($this->resource)['qty']??0,
//            'dlv' => optional($this->resource)['dlv'],
//            'p' => optional($this->resource)['p'],
//            'display_p' => (new Moneys())->setAmount(optional($this->resource)['p'])->format(inc:true,onlyTax:  true),
//            'ppp' => optional($this->resource)['ppp'],
//            'display_ppp' => (new Moneys())->setAmount(optional($this->resource)['ppp'])->format(),
//            'selling_price_ex' => optional($this->resource)['selling_price_ex'],
//            'display_selling_price_ex' => (new Moneys())->setAmount(optional($this->resource)['selling_price_ex'])->format(),
//            'selling_price_inc' => optional($this->resource)['selling_price_inc'],
//            'display_selling_price_inc' => (new Moneys())->setAmount(optional($this->resource)['selling_price_inc'])->format(),
//            'profit' => optional($this->resource)['profit'],
//            'display_profit' => (new Moneys())->setAmount(optional($this->resource)['profit'])->format(),
//            'vat' => floatval(optional($this->resource)['vat']),
//            'vat_p' => (new Moneys())->setAmount(optional($this->resource)['p'])->setTax(optional($this->resource)['vat'])->amount(onlyTax: true),
//            'display_vat_p' =>(new Moneys())->setAmount(optional($this->resource)['p'])->setTax(optional($this->resource)['vat'])->format(onlyTax: true),
//            'vat_ppp' => (new Moneys())->setAmount(optional($this->resource)['ppp'])->setTax(optional($this->resource)['vat'])->amount(onlyTax: true),
//            'display_vat_ppp' => (new Moneys())->setAmount(optional($this->resource)['ppp'])->setTax(optional($this->resource)['vat'])->format(onlyTax: true),
//            'gross_price' => optional($this->resource)['gross_price'],
//            'display_gross_price' => (new Moneys())->setAmount(optional($this->resource)['gross_price'])->format(),
//            'gross_ppp' => optional($this->resource)['gross_ppp'],
//            'display_gross_ppp' => (new Moneys())->setAmount(optional($this->resource)['gross_ppp'])->format(),
//            'discount' => $this->displayDiscountAndMargin(optional($this->resource)['discount']),
//            'margins' => $this->displayDiscountAndMargin(optional($this->resource)['margins']),
//        ];
    }

    /**
     * @param array $data
     * @return array
     */
//    public function displayDiscountAndMargin(
//        array $data
//    ): array
//    {
//        if (optional($data)['type'] && optional($data)['value']) {
//            if ($data['type'] === "fixed") {
//                $data['display_value'] = (new Moneys())->setAmount($data['value']/ 1000)->format();
//            } else {
//                $data['display_value'] = $data['value'];
//            }
//        }
//
//        return $data;
//    }

}

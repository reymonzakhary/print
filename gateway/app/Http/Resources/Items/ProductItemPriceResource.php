<?php

namespace App\Http\Resources\Items;

use App\Plugins\Moneys;
use App\Plugins\PriceFormatter;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ProductItemPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        // @TODO check the response before removing the comments this is a new helper of the prices
        return PriceFormatter::format(is_array($this)? $this: $this->resource);
//        return [
//            "id" => optional($this)['id'],
//            'pm' => optional($this)['pm'],
//            "p" => optional($this)['p'],
//            "display_p" => moneys()->setAmount(optional($this)['p'])->format(),
//
//            "dlv" => optional($this)['dlv'],
//
//            "ppp" => optional($this)['ppp'],
//            "display_ppp" => \moneys()->setPrecision(5)->setAmount(optional($this)['ppp'])->format(),
//
//            "qty" => optional($this)['qty'],
//
//            "vat" => sanitizeToInt($this['vat'] ?? ''),
//            "vat_p" => moneys()->setTax(sanitizeToInt($this['vat'] ?? ''))
//                ->setAmount(optional($this)['selling_price_ex'])
//                ->amount(false, true),
//            "display_vat_p" => moneys()->setTax(sanitizeToInt($this['vat'] ?? 0))
//                ->setAmount(optional($this)['selling_price_ex'])
//                ->format(false, true),
//
//            "vat_total_p" => moneys()->setTax(sanitizeToInt($this['vat'] ?? ''))
//                ->setAmount(optional($this)['selling_price_inc_shipping'])
//                ->amount(false, true),
//            "display_vat_total_p" => moneys()->setTax(sanitizeToInt($this['vat'] ?? 0))
//                ->setAmount(optional($this)['selling_price_inc_shipping'])
//                ->format(false, true),
//
//            "profit" => optional($this)['profit'],
//            "display_profit" => ((new Moneys())->setAmount(optional($this)['profit']))->format(),
//
//            "margins" => optional($this)['margins'],
//
//            "vat_ppp" => optional($this)['vat_ppp'],
//            "display_vat_ppp" => ((new Moneys())->setAmount(optional($this)['vat_ppp']))->format(),
//
//            "discount" => optional($this)['discount'],
//
//            "gross_ppp" => optional($this)['gross_ppp'],
//            "display_gross_ppp" => ((new Moneys())->setAmount(optional($this)['gross_ppp']))->format(),
//
//            "gross_price" => optional($this)['gross_price'],
//            "display_gross_price" => ((new Moneys())->setAmount(optional($this)['gross_price']))->format(),
//
//            "selling_price_ex" => moneys()->setTax(sanitizeToInt($this['vat'] ?? ''))
//                ->setAmount(optional($this)['selling_price_ex'])
//                ->amount(),
//            "display_selling_price_ex" => moneys()->setTax(sanitizeToInt($this['vat'] ?? 0))
//                ->setAmount(optional($this)['selling_price_ex'])
//                ->format(),
//
//            "selling_price_inc" => moneys()->setTax(sanitizeToInt($this['vat'] ?? ''))
//                ->setAmount(optional($this)['selling_price_ex'])
//                ->amount(true),
//            "display_selling_price_inc" => moneys()->setTax(sanitizeToInt($this['vat'] ?? 0))
//                ->setAmount(optional($this)['selling_price_ex'])
//                ->format(true),
//
//
//            "selling_price" => moneys()->setTax(sanitizeToInt($this['vat'] ?? ''))
//                ->setAmount(optional($this)['selling_price_inc_shipping'])
//                ->amount(),
//            "display_selling_price" => moneys()->setTax(sanitizeToInt($this['vat'] ?? 0))
//                ->setAmount(optional($this)['selling_price_inc_shipping'])
//                ->format(),
//
//            "selling_price_total" => moneys()->setTax(sanitizeToInt($this['vat'] ?? ''))
//                ->setAmount(optional($this)['selling_price_inc_shipping'])
//                ->amount(true),
//            "display_selling_price_total" => moneys()->setTax(sanitizeToInt($this['vat'] ?? 0))
//                ->setAmount(optional($this)['selling_price_inc_shipping'])
//                ->format(true),
//
//            "shipping_cost" => optional($this)['shipping_cost'],
//
//        ];

    }
}

<?php

namespace Modules\Cms\Transformers\Snippets\Account\Orders\Items;

use App\Plugins\Moneys;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPriceTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'pm' => optional($this)['pm'],
            'dlv' => optional($this)['dlv'],
            'qty' => optional($this)['qty'],
            'supplier_product' => optional($this)['supplier_product'],
            'p' => optional($this)['p'],
            'ppp' => optional($this)['ppp'],
            'display_p' => ((new Moneys())->setAmount(optional($this)['p']))->format(),
            'display_ppp' => ((new Moneys())->setAmount(optional($this)['ppp']))->format(),
            'resale_p' => optional($this)['resale_p'],
            'resale_ppp' => (int)optional($this)['resale_p'] ? (int)optional($this)['resale_p'] / (int)$this['qty'] : 0,
            'display_resale_p' => ((new Moneys())->setAmount(optional($this)['resale_p']))->format(),
            'display_resale_ppp' => (int)optional($this)['resale_p'] ?
                ((new Moneys())->setAmount((int)optional($this)['resale_p'] / (int)$this['qty']))->format() :
                ((new Moneys())->setAmount(0))->format(),
        ];
    }
}

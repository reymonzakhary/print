<?php

namespace App\Http\Resources\Cart;

use App\Foundation\Settings\Settings;
use App\Http\Resources\Products\ProductIndexResource;
use App\Http\Resources\Statuses\StatusResource;
use App\Models\Tenants\Box;
use App\Models\Tenants\Option;
use App\Plugins\Moneys;
use Illuminate\Http\Request;
use JsonException;

/**
 * Class CartProductVariationResource
 * @package App\Http\Resources\Cart
 * @OA\Schema(
 *     schema="CartProductVariationResource",
 *     title="Product Resource"
 *
 * )
 */
class CartProductVariationResource extends ProductIndexResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     * @throws JsonException
     */

    /**
     * @OA\Property(property="Product",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="id", type="string", example=1),
     *          @OA\Property(property="cart_id", type="string", example=1),
     *          @OA\Property(property="sku_id", type="string", example=3),
     *          @OA\Property(property="product_id", type="string", example=2),
     *          @OA\Property(property="variation", type="string", example="[]"),
     *          @OA\Property(property="qty", type="string", example=100),
     *        )
     *     ),
     */
    public function toArray($request)
    {
        return match (is_numeric($this->sku_id)) {
            false => $this->Mongo($request),
            true => $this->customProduct($request)
        };
    }

    /**
     * @param Request $request
     * @return array
     */
    public function customProduct(Request $request): array
    {
        $product = $this->resource->sku;

        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'variation_id' => $this->id,
            'sku_id' => $this->sku_id,
            'status' => StatusResource::make($this->st),
            'product_id' => $product->product->row_id,
            'sku' => $product->sku,
            'ean' => $product->ean,
            'name' => $product->product->name,
            'description' => $product->product->description,
            'vat_id' => $product->product->vat_id,
            'vat' => Settings::vat(),
            'expire_date' => $product->product->expire_date,
            'expire_after' => $product->product->expire_after,
            'unit_id' => $product->product->unit_id,
            'brand_id' => $product->product->brand_id,
            'category_id' => $product->product->category_id,
            'iso' => $product->product->iso,
            'properties' => $product->product->properties,
            'media' => $product->product->media,
            'attachments' => CartMediaResource::collection(optional($this->userMedia(auth()->user()->id)->get())->where('disk', '!=', 'local')),
            'variations' => !collect($this->variation)->where('variation')
                ? []
                : $this->variations($this->variation),
            ///////// this have to updated to get the box and all the selected options //////
            'qty' => $this->qty,
            "reference" => $this->reference,
            "display_price" => $this->price->format(),
            "price" => $this->price->amount(),
            "display_subtotal" => $this->price->multiply($this->qty)->format(),
            "subtotal" => $this->price->multiply($this->qty)->amount(),
            "display_tax" => '(%' . Settings::vat() . ') ' . $this->price->setTax(Settings::vat())->format(false,true),
            "tax" => $this->price->setTax(Settings::vat())->amount(false,true),
            "display_total" => $this->price->multiply($this->qty)->format(true),
            "total" => $this->price->multiply($this->qty)->amount(true),
        ];
    }


    private function Mongo($request)
    {
        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'variation_id' => $this->id,
            'sku_id' => $this->sku_id,
            'status' => StatusResource::make($this->st),
            'product_id' =>  $this->product_id,
            'sku' => $this->sku,
            'ean' => $this->ean,
            'category_id' => collect($this->variation)->get('category_id'),
            'name' => collect(optional($this->variation->category)['display_name'])->firstWhere('iso', app()->getLocale())['display_name'],
            'description' => null,
            'vat' => optional($this->variation->price)['vat'],
            'expire_date' => null,
            'expire_after' => null,
            'unit_id' => null,
            'brand_id' => null,
            'iso' => app()->getLocale(),
            'properties' => null,

            'media' => $this->media,
            'attachments' => CartMediaResource::collection(optional($this->userMedia(auth()->user()->id)->get())->where('disk', '!=', 'local')),
            'variation' => $this->variation,
            'qty' => $this->qty,
            /// check this
            "reference" => $this->reference,
            "display_price" => moneys()->setPrecision(5)->setAmount(optional($this->variation->price)['ppp'])->format(),
            "price" => $this->price->amount(),
            "display_subtotal" => moneys()->setAmount(optional($this->variation->price)['selling_price_ex'])->format(),
            "subtotal" => moneys()->setAmount(optional($this->variation->price)['selling_price_ex'])->amount(),
            "display_tax" => '(%' . optional($this->variation->price)['vat'] . ') ' . \moneys()->setAmount(optional($this->variation->price)['vat_ppp']),
            "tax" => \moneys()->setAmount(optional($this->variation->price)['vat_ppp']),
            "display_total" => moneys()->setAmount(optional($this->variation->price)['selling_price_ex'])->format(true),
            "total" => moneys()->setAmount(optional($this->variation->price)['selling_price_ex'])->amount(true),
        ];
    }

    private function variations($va)
    {
        return collect($va)->groupBy('box_id')->map(function ($v) {
            if(!optional(optional(optional($v))['variation'])) {
                $box = Box::where('row_id', optional(optional(optional($v))['variation'])['box_id'])->first();
                return [
                    'name' => $box->name,
                    'options' => collect($v)->map(fn($option) => Option::where('row_id', $option['variation']['option_id'])->first())
                ];
            }

        })->values();
    }
}

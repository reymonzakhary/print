<?php

namespace App\Validators;

use App\Http\Requests\Cart\CartStoreRequest;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sku;
use Illuminate\Validation\ValidationException;

class PrepareCartVariationValidator
{

    private array|object $product;
    private $request = null;
    private $response = null;
    private $id = null;
    private $quantity = null;
    private $product_id = null;
    private $variations = null;
    private $mongo = false;


    // check product
    private bool $single = true;
    private bool $hasVariation = false;


    // saved product
    private Product $saverdProduct;


    public function __construct(
        CartStoreRequest $request
    )
    {
        $this->request = $request->merge([
            'sku' => null,
            'product_id' => null,
        ]);

        $this->product = $this->getProductFrom($request->product);
        $this->product_id = null;
        $this->variations = $request->variations ?? [];
        $this->quantity = $request->quantity;
    }

    public function getProductFrom($product)
    {
        return (is_numeric($product)) ? $this->getFromCustomProduct($product) : $this->getFromMongo($product);
    }

    public function getFromCustomProduct($sku)
    {
        if ($sku = Sku::find($sku)) {
            $this->saverdProduct = $sku->product;
            $this->hasVariation = $this->saverdProduct->variation && !$this->saverdProduct->excludes;
            $this->single = !($this->saverdProduct->variation && $this->saverdProduct->excludes);
            return $sku;
        }

        throw ValidationException::withMessages([
            'product' => [
                __('Product not exists!')
            ]
        ]);
    }

    public function getFromMongo($product)
    {
        /**
         * Todo :: check if this product is exist return it from mongo database else throw exception
         */
        if ($product && is_array($product)) {
            $this->mongo = true;
            return $product;
        }

        throw ValidationException::withMessages([
            'product' => [
                __('Product not exists!')
            ]
        ]);
    }

    public function product_id()
    {
        return $this->mongo ? optional(optional($this->product)['prices'])['supplier_product'] : $this->product->id;
    }

    public function sku()
    {
        return $this->mongo ? null : $this->product;
    }

    public function product()
    {
        return $this->mongo ? $this->product : $this->product->toArray();
    }


    public function variations()
    {
        if ($this->mongo || empty($this->variations)) {
            return $this->variations;
        }
        if ($this->single) {
            if ($this->hasVariation) {
                $variations = $this->saverdProduct->variations()->get();
                return collect($this->variations)->map(function ($v) use ($variations) {
                    if ($variation = $variations->where('id', $v["id"])->first()) {
                        $variation = $variation->toArray();
                        $variation['price'] = $variation['price']->amount();
                        return $variation;
                    }
                    return throw ValidationException::withMessages([
                        'variations' => __("variation `:variation` doesn't exists", ['variation' => $v["id"]])
                    ]);
                })->toArray();
            }

            throw ValidationException::withMessages([
                'variations' => __('This Product doesn\'t have any variations')
            ]);
        } else {
            return throw ValidationException::withMessages([
                'variations' => __('This variations doesn\'t exists')
            ]);
        }

    }


    final public function delivery(): array
    {
        $methods = get_class_methods($this);
        $ex = [
            '__construct',
            'prepare',
            'delivery',
            'getFromCustomProduct',
            'getFromMongo',
            'getProductFrom'
        ];

        return array_values(array_diff($methods, $ex));

    }


    final public function prepare(): ?array
    {
        $methods = $this->delivery();
        foreach ($methods as $method) {
            if (array_key_exists($method, $this->request->toArray())) {
                $this->response[$method] = $this->{$method}();
            } else {
                $this->response[$method] = $this->{$method};
            }
        }
        return $this->response;
    }
}

<?php

namespace App\Http\Controllers\Tenant\Web\Cart;

use App\Cart\Cart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CartStoreRequest;
use App\Http\Requests\Cart\CartUpdateRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\Tenants\Product;
use App\Models\Tenants\UserCart;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CartController extends Controller
{

    /**
     * @var Cart
     */
    protected Cart $cart;

    /**
     * CartController constructor.
     * @param Cart $cart
     */
    public function __construct(Cart $cart)
    {
        $this->middleware('auth:tenant');
        $this->cart = $cart;
    }

    /**
     * @param Request $request
     * @param Cart    $cart
     * @return CartResource
     */
    public function index(
        Request $request,
        Cart    $cart
    )
    {
        $cart->sync();
        $request->user()->load([
            'cart.variations.stock', 'cart.variations',
            'cart.variations.box'
        ]);

        return (new CartResource($request->user()))
            ->additional([
                'meta' => $this->meta($cart)
            ]);
    }

    /**
     * @param CartStoreRequest $request
     * @param Cart             $cart
     */
    public function store(
        CartStoreRequest $request,
        Cart             $cart
    )
    {
        $cart->add($request->products);
        $cart->sync();

        return response()->json([
            'message' => __('Items added successfully'),
            'status' => Response::HTTP_CREATED
        ], Response::HTTP_CREATED);
    }

    /**
     * @param CartUpdateRequest $request
     * @param UserCart          $product
     * @param Cart              $cart
     * @return CartResource
     */
    public function update(
        CartUpdateRequest $request,
        UserCart          $product,
        Cart              $cart
    )
    {
        $product->update([
            "product_type" => $request->product_type,
            "variations" => $request->variations,
            "quantity" => $request->quantity
        ]);
        $cart->sync();
        return (new CartResource($request->user()))
            ->additional([
                'meta' => $this->meta($cart)
            ]);
    }

    /**
     * @param Request $request
     * @param Product $product
     * @param Cart    $cart
     * @return CartResource
     */
    public function destroy(
        Request $request,
        Product $product,
        Cart    $cart
    )
    {
        $cart->delete($product->id);
        return (new CartResource($request->user()))
            ->additional([
                'meta' => $this->meta($cart)
            ]);
    }

    /**
     * @param Cart $cart
     * @return array
     */
    protected function meta(
        Cart $cart
    )
    {
        return [
            'empty' => $cart->isEmpty(),
            'subtotal' => $cart->subtotal()->format(),
            'total' => $cart->total()->format(),
            'changed' => $cart->hasChanged(),
        ];
    }
}

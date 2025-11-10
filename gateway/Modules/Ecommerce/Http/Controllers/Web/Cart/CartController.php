<?php

namespace Modules\Ecommerce\Http\Controllers\Web\Cart;

use App\Cart\Cart;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JsonException;
use Modules\Ecommerce\Http\Requests\Cart\CartStoreRequest;
use Modules\Ecommerce\Http\Requests\Cart\CartUpdateRequest;
use Modules\Ecommerce\Http\Resources\Cart\CartResource;

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
        $this->middleware('auth');
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
        dd($request->all());
        $cart->sync();
//        $request->user()->load(['cart.variations.stock','cart.variations', 'cart.stock']);

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
     * @param Product           $product
     * @param Cart              $cart
     * @throws JsonException
     */
    public function update(
        CartUpdateRequest $request,
        Product           $product,
        Cart              $cart
    )
    {
        $cart->update(
            $product->id,
            $request->product_type,
            $request->variations,
            $request->quantity
        );
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

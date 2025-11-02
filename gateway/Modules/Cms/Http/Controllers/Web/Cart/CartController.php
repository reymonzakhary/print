<?php

namespace Modules\Cms\Http\Controllers\Web\Cart;

use App\Cart\Cart;
use App\Http\Requests\Cart\CartStoreRequest;
use App\Http\Requests\Cart\CartUpdateRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\Tenants\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use JsonException;

class CartController extends Controller
{
    /**
     * @var Cart
     */
    protected Cart $cart;

    /**
     * CartController constructor.
     * @param Request $request
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     * @return CartResource
     */
    public function index(
        Request $request
    )
    {
        $cart = new Cart($request->user());
        $cart->sync();
        return (new CartResource($request->user()))
            ->additional([
                'meta' => $this->meta($cart)
            ]);
    }

    /**
     * @param CartStoreRequest $request
     * @return JsonResponse
     */
    public function store(
        CartStoreRequest $request
    )
    {
        $cart = new Cart($request->user());
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
     * @return CartResource
     * @throws JsonException
     */
    public function update(
        CartUpdateRequest $request,
        Product           $product
    )
    {
        $cart = new Cart($request->user());
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
     * @return CartResource
     */
    public function destroy(
        Request $request,
        Product $product
    )
    {
        $cart = new Cart($request->user());
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

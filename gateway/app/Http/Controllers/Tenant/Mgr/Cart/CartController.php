<?php

namespace App\Http\Controllers\Tenant\Mgr\Cart;

use App\Blueprints\Contracts\BlueprintContactInterface;
use App\Cart\Contracts\CartContractInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CartStoreRequest;
use App\Http\Requests\Cart\CartUpdateRequest;
use App\Http\Resources\Cart\CartResource;
use App\Models\Tenant\CartVariation;
use App\Shop\Contracts\ShopProductInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    /**
     * @param Request               $request
     * @param CartContractInterface $cart
     * @return CartResource
     *
     * @return CartResource
     * @OA\Get (
     *     tags={"Cart"},
     *     path="/api/v1/mgr/cart",
     *     summary="Get All Cart Products",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200",
     *  description="",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/CartResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example=""),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function index(
        Request               $request,
        CartContractInterface $cart
    )
    {

        return (new CartResource($cart->contents()))
            ->additional([
                'meta' => $this->meta($cart)
            ]);
    }

    /**
     * @param CartContractInterface     $cart
     * @param CartStoreRequest          $request
     * @param BlueprintContactInterface $blueprint
     * @return JsonResponse
     * @throws BindingResolutionException
     * @throws ValidationException|\Throwable
     * @OA\Post (
     *     tags={"Cart"},
     *     path="/api/v1/mgr/cart",
     *     summary="Create Cart",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="products setting data",
     *      @OA\JsonContent(ref="#/components/schemas/CartStoreRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="",
     *     @OA\JsonContent(
     * @OA\Property(type="string", title="message", description="message", property="message", example="Items added successfully."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function store(
        CartContractInterface     $cart,
        CartStoreRequest          $request,
        BlueprintContactInterface $blueprint
    )
    {
        if ($request->mode == 'print') {
            $cart->addPrintingProduct(
                $request->only(
                    'custom',
                    'signature',
                    'product_id',
                    'product_name',
                    'product_slug',
                    'variation',
                    'type',
                    'calculation_type',
                    'items',
                    'product',
                    'connection',
                    'tenant_id',
                    'tenant_name',
                    'external',
                    'external_id',
                    'external_name',
                    'variations',
                    'category',
                    'margins',
                    'divided',
                    'quantity',
                    'calculation',
                    'hasVariation',
                    'price'
                ),
                $request->quantity,
                moneys()->setAmount(optional($request->price)['gross_ppp'])->multiply(100)->amount(),
                $request->file('files')??[]
            );

            return response()->json([
                'message' => __('Items added successfully'),
                'status' => Response::HTTP_CREATED
            ], Response::HTTP_CREATED);

        }

        return response()->json([
            'data' => $cart->apply(
                $blueprint,
                ...$request->only(['product', 'quantity', 'ns', 'variations', 'mode', 'sku', 'template', 'type', 'files'])
            ),
            'message' => __('Items added successfully'),
            'status' => Response::HTTP_CREATED
        ], Response::HTTP_CREATED);
    }


    /**
     * @param CartUpdateRequest     $request
     * @param CartVariation         $product
     * @param CartContractInterface $cart
     * @return JsonResponse
     * @OA\Put (
     *     tags={"Cart"},
     *     path="/api/v1/mgr/cart/{id}",
     *     summary="Update Cart",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="products setting data",
     *      @OA\JsonContent(ref="#/components/schemas/CartUpdateRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/CartResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example="Items updated successfully."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function update(
        CartUpdateRequest     $request,
        CartVariation         $product,
        CartContractInterface $cart,
    )
    {
        if ($cart->update($product, $request)) {
            return response()->json([
                'message' => __('Cart Item has been Updated'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('We could not find the requested item'),
            'status' => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param Request               $request
     * @param CartVariation         $product
     * @param CartContractInterface $cart
     * @return JsonResponse
     * @OA\Delete  (
     *     tags={"Cart"},
     *     path="/api/v1/mgr/cart/{id}",
     *     summary="Delete Cart",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="",
     *     @OA\JsonContent(
     * @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/CartResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example="Item has been deleted successfully."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function destroy(
        Request               $request,
        CartVariation         $product,
        CartContractInterface $cart
    ): JsonResponse
    {
        $queues = $product->queues()->where('queues.id', optional(optional($product->variation)['blueprint'])['queue_id'])->get();
        if ($product->delete()) {
            $queues->each(fn($queue) => $queue->delete());
            return \response()->json([
                'message' => __('Cart Item has been Deleted'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return \response()->json([
            'message' => __('Cart item wasn\'t deleted, please try again later.' ),
            'status' => Response::HTTP_NOT_ACCEPTABLE
        ], Response::HTTP_NOT_ACCEPTABLE);
    }

    /**
     * @param CartContractInterface $cart
     * @return array
     */
    protected function meta(
        CartContractInterface $cart
    )
    {
        return [
            'empty' => $cart->isEmpty(),
            'subtotal' => $cart->subtotal()->format(),
            'vat' => $cart->vat()->format(),
            'total' => $cart->total()->format(),
            'changed' => $cart->hasChanged(),
        ];
    }


}

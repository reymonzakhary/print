<?php

namespace Modules\Ecommerce\Http\Controllers\Web\Orders;

use App\Events\Tenant\Order\CreatedItemOrderEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Orders\OrderActivityResource;
use App\Http\Resources\Orders\OrderResource;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Ecommerce\Cart\Cart;
use Modules\Ecommerce\Http\Requests\Order\OrderStoreRequest;
use Modules\Ecommerce\Http\Requests\Order\OrderUpdateRequest;

class OrderController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        // @todo
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return OrderResource|JsonResponse
     */
    public function show(
        int $id
    )
    {
        if ($order = Order::find($id)) {
            return OrderResource::make($order)
                ->hide(
                    ['shipping_cost', 'payment_reference', 'invoice_nr']
                )->hideChildren(
                    [
                        'status' => [
                            'id', 'created_at', 'updated_at'
                        ],
                        'context' => [
                            'description', 'config'
                        ],
                        'customer' => [
                            'username',
                            'email_verified_at', 'created_at',
                            'updated_at', 'custom_field',
                            'bio', 'dob'
                        ]
                    ]
                )
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }

        /**->cart
         * error response
         */
        return response()->json([
            'message' => __('orders.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OrderStoreRequest $request
     * @return Model
     */
    public function store(
        OrderStoreRequest $request
    )
    {
        $cart = new Cart($request->user());
        // check if not empty ?
        if (!$cart->isEmpty()) {
            $validated = array_merge(
                $request->validated(),
                ['price' => $cart->total()->amount()]
            );
            // create order
            $order = Order::create($validated);
            // add items to the order
            $this->getItemspayload($order, $request->user());
            $cart->empty();
            return $order->with(['items' => function ($q) {
                return $q->orderBy('created_at', 'desc');
            }])->where('id', $order->id)->first();
        }
        abort(404, 'cart empty');
    }

    public function getItemspayload($order, $user)
    {
        return $user->cart->map(function ($cartItem) use ($order, $user) {
            $qty = $cartItem->pivot->quantity;
            $shippingCost = 0;
            $item = new Item();
            $item->supplier_id = request()->tenant->uuid;
            $item->product = json_encode($cartItem, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR, 512);
            $item->save();
            $order->items()->save($item, ['qty' => $qty, 'shipping_cost' => $shippingCost, 'product_id' => $cartItem->id]);
            event(new CreatedItemOrderEvent($order, $item, $user));
        })->toArray();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OrderUpdateRequest $request
     * @param int                $id
     * @return OrderResource|JsonResponse
     */
    public function update(
        OrderUpdateRequest $request,
        int                $id
    )
    {

        if (
            $this->order->update($id, $request->all())
        ) {

            return OrderResource::make(
                $this->order->show($id)
            )
                ->hide(
                    ['shipping_cost', 'payment_reference', 'invoice_nr']
                )->hideChildren(
                    [
                        'status' => [
                            'id', 'created_at', 'updated_at'
                        ],
                        'context' => [
                            'description', 'config'
                        ],
                        'customer' => [
                            'username',
                            'email_verified_at', 'created_at',
                            'updated_at', 'custom_field',
                            'bio', 'dob'
                        ]
                    ]
                )
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => __('orders.updated')
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('orders.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse|void
     */
    public function destroy(
        int $id
    )
    {
        if (auth()->user()->isAbleTo('delete-orders') && $this->order->delete($id)) {
            return response()->json([
                'message' => __('orders.order_removed'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('orders.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function history(
        Order $order
    )
    {
        $orderHistory = $order->history()->orderBy('id', 'desc')->paginate($this->per_page);

        return OrderActivityResource::collection($orderHistory)
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

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

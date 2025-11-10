<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Items;

use App\Enums\Status;
use App\Enums\Status as EnumsStatus;
use App\Events\Tenant\Order\CreateOrderForCustomerEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Items\ItemStoreRequest;
use App\Http\Requests\Items\OrderItemUpdateRequest;
use App\Http\Resources\Items\ItemResource;
use App\Http\Resources\Items\OrderItemResource;
use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use App\Repositories\ItemRepository;
use App\Utilities\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ItemController extends Controller
{
    /**
     * @var ItemRepository
     */
    protected ItemRepository $items;

    /**
     * default hiding field from response
     */
    protected array $hide;

    /**
     * default total result in one page
     */
    protected int $per_page = 5;

    /**
     * UserController constructor.
     *
     * @param Request $request
     * @param Item $item
     * @param OrderService $orderService
     */
    public function __construct(
        Request $request,
        Item $item,
        private readonly OrderService $orderService,
    ) {
        $this->items = new ItemRepository($item);

        /**
         * default hidden field
         */
        $this->hide = [];

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Order $order
     * @return OrderItemResource
     */
    public function index(
        Order $order
    ): OrderItemResource {
        $this->items->order = $order;

        return OrderItemResource::collection($this->items->all())->hide($this->hide);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Order $order
     * @param ItemStoreRequest $request
     *
     * @return ItemResource
     *
     * @throws Throwable
     */
    public function store(
        Order $order,
        ItemStoreRequest $request
    ): ItemResource {
        $this->items->order = $order;
        $item = $this->items->create($request->validated());

        $this->orderService->makeOrderBasedOnItems($order);

        return ItemResource::make($this->items->show($item->id))
            ->hide($this->hide)
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * @param Order $order
     * @param int $id
     * @return OrderItemResource|JsonResponse
     */
    public function show(
        Order $order,
        int $id
    ): OrderItemResource|JsonResponse {
        $this->items->order = $order;
        if (!$order->items()->where('items.id', $id)->exists()) {
            return response()->json([
                'message' => __('items.not_found'),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        return OrderItemResource::make(
            $this->items->show($id)
        )->hide($this->hide)
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * @param OrderItemUpdateRequest $request
     * @param Order $order
     * @param int $id
     *
     * @return OrderItemResource|JsonResponse
     *
     * @throws Throwable
     */
    public function update(
        OrderItemUpdateRequest $request,
        Order $order,
        int $id
    ): OrderItemResource|JsonResponse
    {
        $this->items->order = $order;
        if (!$order->items()->where('items.id', $id)->exists()) {
            return response()->json([
                'message' => __('items.not_found'),
                'status' => Response::HTTP_NOT_FOUND

            ], Response::HTTP_NOT_FOUND);
        }

        if ($this->items->update($id, $request->all())) {
            $this->orderService->makeOrderBasedOnItems($order);
        $item = $order->items()->where('items.id', $id)->first();
        if (!$item->internal && optional($item->product)->order_ref){
            $current_tenant = tenant()->uuid;
            switchSupplier($item->connection);
            $reseller_order = Order::find($item->product->order_ref);
            $this->orderService->makeOrderBasedOnItems($reseller_order);
            switchSupplier($current_tenant);
        }

            return OrderItemResource::make(
                $item
            )
                ->hide($this->hide)
                ->additional([
                    'message' => __('Item has been updated successfully.'),
                    'status' => Response::HTTP_OK
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('items.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Order $order
     * @param int $id
     *
     * @return JsonResponse|Response
     *
     * @throws Throwable
     */
    public function destroy(
        Order $order,
        int $id
    ): JsonResponse|Response {
        $this->items->order = $order;

        if (!$order->items()->where('items.id', $id)->exists()) {
            return response()->json([
                'message' => __('items.not_found'),
                'status' => Response::HTTP_NOT_FOUND

            ], Response::HTTP_NOT_FOUND);
        }

        if ($this->items->delete($id)) {
            $this->orderService->makeOrderBasedOnItems($order);

            /**
             * error response
             */
            return response()->json([
                'data' => [
                    'message' => __('items.item_removed'),
                    'status' => Response::HTTP_ACCEPTED
                ]
            ], Response::HTTP_ACCEPTED);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('item.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }
}

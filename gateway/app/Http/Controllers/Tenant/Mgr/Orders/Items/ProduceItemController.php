<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Orders\Items;

use App\Events\Produce\SendOrderToProducerEvent;
use App\Foundation\Status\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ProduceItemsRequest;
use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ProduceItemController extends Controller
{
    /**
     * @OA\Post (
     *     tags={"Items Produce"},
     *     path="/api/v1/mgr/orders/{order_id}/items/{item_id}/produce",
     *     summary="Send Order Produce",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     * @OA\Property(type="string", title="data", description="data", property="data", example=null),
     * @OA\Property(type="string", title="message", description="message", property="message", example="Xml Send to Produce"),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     * @param Order $order
     * @param Item $item
     * @param ProduceItemsRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function __invoke(
        Order   $order,
        Item    $item,
        Request $request
    ): JsonResponse
    {
        // validate supplier id if exists
        match ($item->st) {
            Status::NEW => [
                $this->updateOrderItemStatus($order, $item),

                match ($item->supplier_id) {
                    $request->tenant->uuid => response()->json(['message' => 'Produce internal']),

                    default => event(new SendOrderToProducerEvent(
                        $order,
                        $item,
                        tenant()->uuid,
                        $request->get('iso') ?? app()->getLocale(),
                        $item->supplier_id
                    ))
                }
            ],

            default => throw ValidationException::withMessages(['item' =>
                __("Item {$item->id} can't be produced, it's already in process or closed")
            ])
        };

        return response()->json([
            'data' => null,
            'message' => __('Order item has been sent successfully, to be produced'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * @param Order $order
     * @param Item $item
     * @return void
     * @throws ValidationException
     */
    private function updateOrderItemStatus(
        Order $order,
        Item $item
    ): void
    {
        if (!$order->orderedBy()->exists()) {
            throw ValidationException::withMessages(['item' =>
                __("Parent order of the item does not have any linked customer")
            ]);
        }

        match ($order->st) {
            Status::NEW => $order->update(['st' => Status::IN_PROGRESS]),

            default => ''
        };

        $item->update(['st' => Status::IN_PROGRESS]);
    }

}

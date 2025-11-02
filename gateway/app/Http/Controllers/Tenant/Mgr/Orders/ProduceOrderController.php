<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders;

use App\Events\Produce\SendOrderToProducerEvent;
use App\Foundation\ContractManager\Facades\ContractManager;
use App\Foundation\Status\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ProduceBulkItemsRequest;
use App\Http\Requests\Order\ProduceItemsRequest;
use App\Models\Hostname;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


class ProduceOrderController extends Controller
{
    /**
     * @OA\Post (
     *     tags={"Order Produce"},
     *     path="/api/v1/mgr/orders/{order_id}/items/produce",
     *     summary="Send Order Produce",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(ref="#/components/schemas/ProduceItemsRequest"),
     *   ),
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
     * @param Order               $order
     * @param ProduceItemsRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function order(
        Order               $order,
        ProduceItemsRequest $request
    )
    {
        if (!in_array($order->st, [Status::NEW, Status::IN_PROGRESS, Status::DONE], true)) {
            throw ValidationException::withMessages(['order' =>
                __('The selected order is not confirmed yet!')
            ]);
        }
        $items = $order->items
            ->whereIn('id', collect($request->validated('items'))->pluck('id'))
            ->groupBy('connection')->each(function ($items, $connection) use ($order, $request) {
                if (tenant()->uuid !== $connection) {
                    /** @todo get the pipeline of the supplier **/

                    $appliedContract = ContractManager::getContractWithSupplierByConnection(Hostname::class , $connection);
                    if (!$appliedContract || !$appliedContract->active || $appliedContract->st != Status::ACCEPTED) {
                        throw ValidationException::withMessages([
                            'contract' => __('There is no contract available between you and the receiving party, please contact the system administrator.')
                        ]);
                    }

                    if ($order->delivery_multiple && empty(collect($items)->first()->addresses()->first())) {
                        throw ValidationException::withMessages([
                            'items' => __('Item must have at least one address to be sent to the producer.')
                        ]);
                    }
                    Item::query()->whereIn('id' , $items->modelKeys())->update([
                        'st' => Status::PROCESSING,
                    ]);
                    event(new SendOrderToProducerEvent(
                        $order,
                        $items,
                        tenant()->uuid,
                        $request->get('iso') ?? app()->getLocale(),
                        $connection ,
                        $appliedContract
                    ));

                } else {
                    /**
                     * @todo Send items to own machine
                     */
                }

            });



        return response()->json([
            'data' => null,
            'message' => __('Xml Send to Produce'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post (
     *     tags={"Order Produce Items"},
     *     path="/api/v1/mgr/orders/produce",
     *     summary="Send Order Produce",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(ref="#/components/schemas/ProduceBulkItemsRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     * @OA\Property(type="string", title="data", description="data", property="data", example=null),
     * @OA\Property(type="string", title="message", description="message", property="message", example="Xml Send to Produce"),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     )
     * @param ProduceBulkItemsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orders(ProduceBulkItemsRequest $request)
    {
        foreach ($request->validated() as $i) {
            $order = Order::find($i['order']);
            app(__CLASS__)->order($order, new ProduceItemsRequest($i));
        }

        return response()->json([
            'data' => null,
            'message' => __('Xml Send to Produce'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }


}

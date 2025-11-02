<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Items\JobTickets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Items\JobTicketStoreRequest;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Repositories\ItemRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;


class JobTicketController extends Controller
{
    protected ItemRepository $items;


    /**
     * @param Order                 $order
     * @param Item                  $item
     * @param string                $format
     * @param JobTicketStoreRequest $request
     * @return JsonResponse|mixed
     */
    public function send(
        JobTicketStoreRequest $request,
        Order                 $order,
        Item                  $item,
        string                $format = "xml"
    )
    {
        if ($item = $order->items->find($item)) {
            $class = "\App\Processors\JobTicketType\\" . Str::ucfirst($format) . "JobTicketProcessor";
            if (class_exists($class)) {
                return (new $class())->format(
                    $order,
                    $item,
                    $request->validated()['iso'],
                    tenant()->uuid
                );
            }
            return \response()->json([
                'message' => __('Service not available at this moment!'),
                'status' => Response::HTTP_NOT_ACCEPTABLE
            ], Response::HTTP_NOT_ACCEPTABLE);

        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('The order item is not found!'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

}

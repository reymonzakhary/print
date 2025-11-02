<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Items\Tags;

use App\Http\Controllers\Controller;
use App\Http\Requests\Items\Tags\ItemTagsRequest;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    public function __invoke(
        ItemTagsRequest $request,
        Order           $order,
        Item            $item
    )
    {
        // check if item belong to order
        if ($order->items()->where('items.id', $item->id)->exists()) {
            $item->tags()->sync($request->validated()['ids']);
            return response()->json([
                'message' => __('Tag has been added successfully'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('We could\'nt find the requested item'),
            'status' => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);

    }
}

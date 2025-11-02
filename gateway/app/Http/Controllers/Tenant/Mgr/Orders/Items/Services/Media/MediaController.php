<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Items\Services\Media;

use App\Events\Tenant\Order\Item\Service\Media\CreateOrderItemServiceMediaEvent;
use App\Events\Tenant\Order\Item\Service\Media\DeleteOrderItemServiceMediaEvent;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Item;
use App\Models\Tenants\Media;
use App\Models\Tenants\Order;
use App\Models\Tenants\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MediaController extends Controller
{
    public function index(
        Order   $order,
        Item    $item,
        Service $service
    )
    {
        if (
            $order->items()->where('items.id', $item->id)->exists() &&
            $item->services()->where('services.id', $service->id)->exists()
        ) {
            return $item->getMedia('item-service');
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('media.no_media_available'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }


    public function store(
        Order   $order,
        Item    $item,
        Service $service,
        Request $request
    )
    {
        if (
            $order->items()->where('items.id', $item->id)->exists() &&
            $item->services()->where('services.id', $service->id)->exists()
        ) {
            $file = $request->file('files');
            foreach ($file as $f) {
                $path = "/orders/{$order->id}/items/{$item->id}/services/{$service->id}";
                $media = $service->addMedia($f, $path, $request->overwrite, $path, 'order-item-services')->getData()->result->fileManager;
                event(new CreateOrderItemServiceMediaEvent($media, $service, $order, $item, auth()->user()));
            }
            // Retrieve the media you just attached.
            return $item->getMedia('order-item-services');
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('media.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    public function destroy(
        Order   $order,
        Item    $item,
        Service $service,
        Media   $media
    )
    {

        if (
            $order->items()->where('items.id', $item->id)->exists() &&
            $item->services()->where('services.id', $service->id)->exists()
        ) {
            $mediaId = $media->id;
            if ($media->delete()) {
                event(new DeleteOrderItemServiceMediaEvent($mediaId, $service, $order, $item, auth()->user()));
                /**
                 * error response
                 */
                return response()->json([
                    'message' => __('media.media_removed'),
                    'status' => Response::HTTP_OK

                ], Response::HTTP_OK);
            }
        }


        /**
         * error response
         */
        return response()->json([
            'message' => __('media.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);

    }
}

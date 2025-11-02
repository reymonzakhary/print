<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Services\Media;

use App\Events\Tenant\Order\Service\Media\CreateOrderServiceMediaEvent;
use App\Events\Tenant\Order\Service\Media\DeleteOrderServiceMediaEvent;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Media;
use App\Models\Tenants\Order;
use App\Models\Tenants\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class MediaController extends Controller
{
    public function index(
        Order   $order,
        Service $service
    )
    {
        if ($order->services()->where('services.id', $service->id)->exists()) {
            return $service->getMedia('order-service');
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('media.no_media_available'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * @param Order   $order
     * @param Service $service
     * @param Request $request
     * @return JsonResponse|Collection
     */
    public function store(
        Order   $order,
        Service $service,
        Request $request
    )
    {
        if ($order->services()->where('services.id', $service->id)->exists()) {
            $file = $request->file('files');
            foreach ($file as $f) {
                $path = "/orders/{$order->id}/services/{$service->id}";
                $media = $service->addMedia($f, $path, $request->overwrite, $path, 'order-services')->getData()->result->fileManager;
                event(new CreateOrderServiceMediaEvent($media, $service, $order, auth()->user()));
            }


            // Retrieve the media you just attached.
            return $service->getMedia('order-services');
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
        Service $service,
        Media   $media
    )
    {

        if ($order->services()->where('services.id', $service->id)->exists()) {
            $mediaId = $media->id;
            if ($media->delete()) {
                event(new DeleteOrderServiceMediaEvent($mediaId, $service, $order, auth()->user()));
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

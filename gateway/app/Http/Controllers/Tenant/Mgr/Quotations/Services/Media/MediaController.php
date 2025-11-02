<?php

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Services\Media;

use App\Events\Tenant\Order\Service\Media\CreateOrderServiceMediaEvent;
use App\Events\Tenant\Order\Service\Media\DeleteOrderServiceMediaEvent;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Media;
use App\Models\Tenants\Quotation;
use App\Models\Tenants\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{

    /**
     * @param Quotation $quotation
     * @param Service   $service
     * @return JsonResponse
     */
    public function index(
        Quotation $quotation,
        Service   $service
    )
    {
        if ($quotation->services()->where('services.id', $service->id)->exists()) {
            return $service->getMedia('order-services');
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
     * @param Quotation $quotation
     * @param Service   $service
     * @param Request   $request
     * @return JsonResponse|Collection
     */
    public function store(
        Quotation $quotation,
        Service   $service,
        Request   $request
    )
    {
        if ($quotation->services()->where('services.id', $service->id)->exists()) {
            $file = $request->file('files');
            foreach ($file as $f) {
                $media = $service->addMedia($f)->toMediaCollection('OrderService');
                event(new CreateOrderServiceMediaEvent($media, $service, $quotation, auth()->user()));
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

    /**
     * @param Quotation $quotation
     * @param Service   $service
     * @param Media     $media
     * @return JsonResponse
     */
    public function destroy(
        Quotation $quotation,
        Service   $service,
        Media     $media
    )
    {
        if ($quotation->services()->where('services.id', $service->id)->exists()) {
            $mediaId = $media->id;
            if ($media->delete()) {
                event(new DeleteOrderServiceMediaEvent($mediaId, $service, $quotation, auth()->user()));
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

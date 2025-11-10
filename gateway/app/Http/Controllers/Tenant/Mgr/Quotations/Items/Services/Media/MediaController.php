<?php

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Items\Services\Media;

use App\Events\Tenant\Order\Item\Service\Media\CreateOrderItemServiceMediaEvent;
use App\Events\Tenant\Order\Item\Service\Media\DeleteOrderItemServiceMediaEvent;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Item;
use App\Models\Tenant\Media;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\Service;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function index(
        Quotation $quotation,
        Item      $item,
        Service   $service
    )
    {
        if (
            $quotation->items()->where('items.id', $item->id)->exists() &&
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
        Quotation $quotation,
        Item      $item,
        Service   $service,
        Request   $request
    )
    {
        if (
            $quotation->items()->where('items.id', $item->id)->exists() &&
            $item->services()->where('services.id', $service->id)->exists()
        ) {
            $file = $request->file('files');
            foreach ($file as $f) {
                $path = "/quotations/{$quotation->id}/items/{$item->id}/services/{$service->id}";
                $media = $service->addMedia($f, $path, $request->overwrite, $path, 'quotation-item-services')->getData()->result->fileManager;
                event(new CreateOrderItemServiceMediaEvent($media, $service, $quotation, $item, auth()->user()));
            }
            // Retrieve the media you just attached.
            return response()->json([
                'message' => __('File has been uploaded successfully!'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
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
        Quotation $quotation,
        Item      $item,
        Service   $service,
        Media     $media
    )
    {

        if (
            $quotation->items()->where('items.id', $item->id)->exists() &&
            $item->services()->where('services.id', $service->id)->exists()
        ) {
            $mediaId = $media->id;
            if ($media->delete()) {
                event(new DeleteOrderItemServiceMediaEvent($mediaId, $service, $quotation, $item, auth()->user()));
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

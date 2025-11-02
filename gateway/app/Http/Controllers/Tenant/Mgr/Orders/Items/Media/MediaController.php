<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Items\Media;

use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use Alexusmai\LaravelFileManager\Events\FilesUploading;
use App\Events\Tenant\Order\Item\Media\DeleteOrderItemMediaEvent;
use App\Http\Controllers\Controller;
use App\Models\Tenants\Item;
use App\Models\Tenants\Media\FileManager;
use App\Models\Tenants\Order;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaController extends Controller
{

    public function index(
        Order $order,
        Item  $item
    )
    {
        if (!auth()->user()->can('orders-items-media-list')) {
            throw ValidationException::withMessages([
                'orders_items_media' => __('Not permitted action.')
            ]);
        }

        if ($order->items()->where('items.id', $item->id)->exists()) {
            return $item->getMedia('order-items');
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
        Request $request
    )
    {
        if (!auth()->user()->can('orders-items-media-create')) {
            throw ValidationException::withMessages([
                'orders_items_media' => __('Not permitted action.')
            ]);
        }

        if ($order->items()->where('items.id', $item->id)->exists()) {
            event(new FilesUploading($request));
            $uploadResponse = $item->addMedia(
                $request->file('files'),
                "/orders/{$order->id}/items/{$item->id}/",
                $request->boolean('overwrite'),
                $request->input('originalPath') . "/orders/{$order->id}/items/{$item->id}/",
                'items'
            );

            event(new FilesUploaded($request));
//            event(new CreateOrderItemMediaEvent($uploadResponse, $order, $item, auth()->user()));

            // Retrieve the media you just attached.
            return $uploadResponse;
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('media.not_found'),
            'status' => Response::HTTP_NOT_FOUND

        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param Order       $order
     * @param Item        $item
     * @param FileManager $fileManager
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(
        Order       $order,
        Item        $item,
        FileManager $fileManager
    )
    {

        if (!auth()->user()->can('orders-items-media-delete')) {
            throw ValidationException::withMessages([
                'orders_items_media' => __('Not permitted action.')
            ]);
        }

        $mediaId = $fileManager->id;

        if ($order->items()->where('items.id', $item->id)->exists() && $fileManager->delete()) {
            Storage::disk($fileManager->disk)->delete(tenant()->uuid . '/' . $fileManager->path  . $fileManager->name);
            event(new DeleteOrderItemMediaEvent($mediaId, $order, $item, auth()->user()));
            /**
             * error response
             */
            return response()->json([
                'message' => __('Media has been deleted successfully'),
                'status' => Response::HTTP_OK

            ], Response::HTTP_OK);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('We could not delete this media'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);

    }

}

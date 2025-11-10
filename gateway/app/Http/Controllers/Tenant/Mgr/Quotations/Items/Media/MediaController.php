<?php

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Items\Media;

use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use Alexusmai\LaravelFileManager\Events\FilesUploading;
use App\Events\Tenant\Order\Item\Media\CreateOrderItemMediaEvent;
use App\Events\Tenant\Order\Item\Media\DeleteOrderItemMediaEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Items\Media\MediaStoreRequest;
use App\Models\Tenant\Item;
use App\Models\Tenant\Media\FileManager;
use App\Models\Tenant\Quotation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{

    public function index(
        Quotation $quotation,
        Item      $item
    )
    {
        if ($quotation->items()->where('items.id', $item->id)->exists()) {
            return $item->getMedia('items');
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
     * Store a new media item for a quotation.
     *
     * @param Quotation $quotation The quotation instance.
     * @param Item $item The item instance.
     * @param MediaStoreRequest $request The request instance.
     * @return JsonResponse The JSON response containing the upload result.
     */
    public function store(
        Quotation $quotation,
        Item      $item,
        MediaStoreRequest   $request
    ): JsonResponse
    {
        $user = auth()->user();
        if ($quotation->items()->where('items.id', $item->id)->exists()) {
            event(new FilesUploading($request));
            $uploadResponse = $item->addMedia(
                $request->file('files') ?? $request->files?->all(),
                "quotations/{$quotation->id}/items/{$item->id}/",
                $request->boolean('overwrite'),
                $request->input('originalPath') . "/quotations/{$quotation->id}/items/{$item->id}/",
                'items'
            );

            event(new FilesUploaded($request));
            event(new CreateOrderItemMediaEvent($uploadResponse->getData()->result?->fileManager, $quotation, $item, $user));

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
     * Delete a media item for a quotation.
     *
     * @param Quotation $quotation The quotation instance.
     * @param Item $item The item instance.
     * @param int $fileManager The file manager ID.
     * @return JsonResponse The JSON response containing the deletion result.
     */
    public function destroy(
        Quotation   $quotation,
        Item        $item,
        int $fileManager
    ): JsonResponse
    {
        $mediaId = $fileManager;
        $fileManager = FileManager::findOrFail($fileManager);
        $user = Auth::user();

        if ($quotation->items()->where('items.id', $item->id)->exists() && $fileManager->delete()) {

            Storage::disk($fileManager->disk)->delete(request()->uuid.'/'.$fileManager->path.$fileManager->name);
            event(new DeleteOrderItemMediaEvent($mediaId, $quotation, $item, $user));
            /**
             * error response
             */
            return response()->json([
                'message' => __('Media has been removed successfully.'),
                'status' => Response::HTTP_OK

            ], Response::HTTP_OK);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('We couldn\'t remove the requested media file.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);

    }

}

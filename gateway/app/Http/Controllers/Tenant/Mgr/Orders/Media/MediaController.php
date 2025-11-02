<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Media;

use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use Alexusmai\LaravelFileManager\Events\FilesUploading;
use App\Events\Tenant\Order\Media\DeleteOrderMediaEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\Media\StoreOrderMediaRequest;
use App\Models\Tenants\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Tenants\Media\FileManager;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    /**
     * retrieving order media
     * @param \App\Models\Tenants\Order $order
     * @return mixed
     */
    public function index(
        Order $order
    )
    {
        return $order->getMedia('attachments');
    }

    /**
     * storing order media
     *
     * @param StoreOrderMediaRequest $request
     * @param Order $order
     * @return JsonResponse
     */
    public function store(
        StoreOrderMediaRequest $request,
        Order $order
    ): JsonResponse
    {
        event(new FilesUploading($request));

        $uploadResponse = $order->addMedia(
            $request->file('files'),
            sprintf('/orders/%s/', $order->getAttribute('id')),
            $request->boolean('overwrite'),
            sprintf('%s/orders/%s/', $request->input('originalPath'), $order->getAttribute('id')),
            'attachments'
        );

        event(new FilesUploaded($request));

        // Retrieve the media you just attached.
        return $uploadResponse;
    }

    /**
     * destroy
     * @param \App\Models\Tenants\Order $order
     * @param \App\Models\Tenants\Media\FileManager $fileManager
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(
        Order $order,
        FileManager $fileManager
    )
    {

        event(new DeleteOrderMediaEvent($fileManager->id, $order, auth()->user()));
        Storage::disk($fileManager->disk)->delete(tenant()->uuid . '/' . $fileManager->path  . $fileManager->name);
        $fileManager->delete();



        return response()->json([
            'message' => __('media.media_removed'),
            'status' => Response::HTTP_OK

        ], Response::HTTP_OK);

    }

}

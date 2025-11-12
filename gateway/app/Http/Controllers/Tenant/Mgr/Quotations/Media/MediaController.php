<?php

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Media;

use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use Alexusmai\LaravelFileManager\Events\FilesUploading;
use App\Events\Tenant\Order\Media\DeleteOrderMediaEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quotation\Media\StoreQuotationMediaRequest;
use App\Models\Tenant\Media\FileManager;
use App\Models\Tenant\Quotation;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    /**
     * retrieving quotation media
     * @param Quotation $quotation
     * @return mixed
     */
    public function index(
        Quotation $quotation
    )
    {
        return $quotation->getMedia('attachments');
    }

    /**
     * storing quotation media
     *
     * @param StoreQuotationMediaRequest $request
     * @param Quotation $quotation
     * @return JsonResponse
     */
    public function store(
        StoreQuotationMediaRequest $request,
        Quotation         $quotation
    ): JsonResponse
    {
        event(new FilesUploading($request));

        $uploadResponse = $quotation->addMedia(
            $request->file('files'),
            sprintf('/quotations/%s/', $quotation->getAttribute('id')),
            $request->boolean('overwrite'),
            sprintf('%s/quotations/%s/', $request->input('originalPath'), $quotation->getAttribute('id')),
            'attachments'
        );

        event(new FilesUploaded($request));

        // Retrieve the media you just attached.
        return $uploadResponse;
    }

    /**
     * destroy
     * @param Quotation $quotation
     * @param FileManager $fileManager
     * @return mixed|JsonResponse
     */
    public function destroy(
        Quotation $quotation,
        FileManager $fileManager
    )
    {

        event(new DeleteOrderMediaEvent($fileManager->id, $quotation, auth()->user()));
        $fileManager->delete();

        return response()->json([
            'message' => __('media.media_removed'),
            'status' => Response::HTTP_OK

        ], Response::HTTP_OK);

    }
}

<?php

namespace App\Http\Controllers\Tenant\Mgr\Cart\Media;

use App\Cart\Contracts\CartContractInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\Media\StoreMediaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{

    /**
     * Summary of store
     * @param \App\Http\Requests\Cart\Media\StoreMediaRequest $request
     * @param \App\Cart\Contracts\CartContractInterface $cart
     * @param int $item
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(
        StoreMediaRequest $request,
        CartContractInterface     $cart,
        int $item
    )
    {
        $path = "/{$cart->id()}/items/{$request->cartVariation->id}";
        $request->cartVariation->addMedias($request->file('files'), $path, 'carts');

        return response()->json([
            'message' => __('media attached successfully'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    public function delete(
        CartContractInterface     $cart,
        int $item,
        int $media
    )
    {
        if (!($item = $cart->contents()->firstWhere('id', $item))) {
            abort(404);
        }

        if (!($media = $item->media()->firstWhere('file_manager.id', $media))) {
            abort(404);
        }

        Storage::disk($media->disk)->delete(tenant()->uuid."{$media->path}/{$media->name}");
        $media->delete();

        return response()->json([
            'message' => __('media deleted successfully'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);

    }

}

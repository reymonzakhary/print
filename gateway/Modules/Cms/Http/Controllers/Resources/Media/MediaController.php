<?php

namespace Modules\Cms\Http\Controllers\Resources\Media;

use App\Http\Resources\Media\MediaResource;
use App\Models\Tenant\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Http\Requests\Media\StoreMediaRequest;
use Modules\Cms\Http\Traits\Tags;

class MediaController extends Controller
{
    use Tags;

    /**
     * Display a listing of the resource.
     * @param string $local
     * @return AnonymousResourceCollection
     */
    public function index(
        Resource $resource
    )
    {
        return MediaResource::collection(
            $resource->where('language', app()->getLocale()
            )->first()->getMedia());
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreMediaRequest $request
     * @param Language          $language
     * @return JsonResponse
     */
    public function store(
        StoreMediaRequest $request,
        Resource          $resource
    )
    {
        return $resource->addMedia(
            $request->file('file'),
            $resource->id,
            true,
            $resource->id,
            $request->collection
        );
    }


}

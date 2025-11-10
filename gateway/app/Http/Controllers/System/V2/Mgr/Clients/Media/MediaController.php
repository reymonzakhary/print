<?php

namespace App\Http\Controllers\System\V2\Mgr\Clients\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\Media\UpdateMediaRequest;
use App\Models\Domain;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    /**
     * @param UpdateMediaRequest $request
     * @param Domain $hostname
     * @return \Illuminate\Http\Response|ResponseFactory
     */
    public function store(
        UpdateMediaRequest $request,
        Domain $hostname
    )
    {
        $website = $hostname?->website()->first();

        Storage::disk('digitalocean')->delete($hostname->logo);

        $storage = Storage::disk('digitalocean')->putFileAs(
            'suppliers',
            $request->file('logo'),
            $website->uuid . '.' . $request->file('logo')->getClientOriginalExtension()
        );

        if ($storage) {
            $hostname->logo = $storage;
            $hostname->save();
        }

        return response([
            'message' => __('Client media updated successfully'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }
}

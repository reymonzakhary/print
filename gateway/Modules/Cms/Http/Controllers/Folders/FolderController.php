<?php

namespace Modules\Cms\Http\Controllers\Folders;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Cms\Entities\Folder;
use Modules\Cms\Http\Requests\Folders\CreateFolderRequest;
use Modules\Cms\Http\Requests\Folders\UpdateFolderRequest;
use Modules\Cms\Transformers\Resource\FolderResource;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return FolderResource::collection(
            Folder::get()
        )->additional([
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return FolderResource
     */
    public function store(
        CreateFolderRequest $request
    )
    {
        return FolderResource::make(Folder::create($request->validated()))
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Show the specified resource.
     * @param string $locale
     * @param Folder $folder
     * @return FolderResource
     */
    public function show(
        Folder $folder
    )
    {
        return FolderResource::make($folder)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateFolderRequest $request
     * @param Folder              $folder
     * @return FolderResource
     */
    public function update(
        UpdateFolderRequest $request,
        Folder              $folder
    )
    {
        $folder->update($request->validated());
        return FolderResource::make($folder)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Remove the specified resource from storage.
     * @param Folder $folder
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(
        Folder $folder
    )
    {
        // clear all relations before deleting the folder
        if ($folder->templates()->exists() || $folder->children()->exists()) {
            /**
             * error response
             */
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => __('Folder couldn\'t be delete')
                ]
            ], Response::HTTP_BAD_REQUEST);
        } elseif ($folder->delete()) {
            /**
             * success response
             */
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_OK,
                    'message' => __('Folder deleted with success')
                ]
            ], Response::HTTP_OK);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('We couldn\'t handle your request, please try again later!')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }
}

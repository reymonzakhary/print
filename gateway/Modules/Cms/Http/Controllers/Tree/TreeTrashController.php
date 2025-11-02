<?php

namespace Modules\Cms\Http\Controllers\Tree;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Transformers\Tree\TreeResource;

class TreeTrashController extends Controller
{
    /**
     * Display a listing of the resource from the trash.
     * @param string $locale
     * @return AnonymousResourceCollection
     */
    public function index()
    {

        $tree = Resource::onlyTrashed()->where('language', app()->getLocale())
            ->with(['children.createdby'])
            ->orderBy('sort', 'ASC')
            ->get([
                'id', 'base_id', 'title', 'parent_id', 'resource_id', 'language', 'isfolder',
                'published', 'hidden', 'hide_children_in_tree', 'created_by'
            ]);

//        $children = Resource::onlyTrashed()->where('language',$locale)
//            ->with(['createdby'])
//            ->whereIn('base_id', $tree->pluck('id')->toArray())
//            ->orderBy('sort', 'ASC')
//            ->get();

        return TreeResource::collection(
            $tree
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param string $locale
     * @param int    $id
     * @return JsonResponse
     */
    public function destroy(
        int $id = null
    )
    {

        if ($id) {
            $resource = Resource::where('id', $id)->withTrashed()->first();
            $resource->children()->forceDelete();
            $resource->forceDelete();
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_OK,
                    'message' => __('Resource has been removed from the trash!')
                ]
            ], Response::HTTP_OK);

        }

        Resource::onlyTrashed()->get()->map(function ($res) {
            $res->children()->forceDelete();
            $res->forceDelete();
        });
        return response()->json([
            'data' => [
                'status' => Response::HTTP_OK,
                'message' => __('Trash has been deleted!')
            ]
        ], Response::HTTP_OK);

    }
}

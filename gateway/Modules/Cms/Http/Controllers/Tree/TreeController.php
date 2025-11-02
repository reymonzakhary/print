<?php

namespace Modules\Cms\Http\Controllers\Tree;

use App\Models\Tenants\Language;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Events\Resources\CreateResourceEvent;
use Modules\Cms\Events\Resources\DeleteResourceEvent;
use Modules\Cms\Events\Resources\UpdateResourceEvent;
use Modules\Cms\Http\Requests\Resources\StoreResourceTreeRequest;
use Modules\Cms\Http\Requests\Resources\UpdateResourceTreeRequest;
use Modules\Cms\Transformers\Tree\TreeResource;

class TreeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param string $local
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        $tree = Resource::where('language', app()->getLocale())
//            ->whereIn('id', $ids)
            ->with(['children'])
            ->orderBy('sort', 'ASC')
            ->isParent()
            ->get([
                'id', 'base_id', 'title', 'parent_id', 'resource_id', 'language', 'isfolder',
                'published', 'hidden', 'hide_children_in_tree', 'created_by'
            ]);

        $children = Resource::where('language', app()->getLocale())
            ->whereIn('base_id', $tree->pluck('id')->toArray())
            ->orderBy('sort', 'ASC')
            ->get();

        return TreeResource::collection(
            $tree->merge($children)
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreResourceTreeRequest $request
     * @return TreeResource
     */
    public function store(
        StoreResourceTreeRequest $request)
    {
        $language = Language::where('iso', app()->getLocale())->firstORFail();
        $resource = Resource::create($request->validated());
        $user = auth()->user();
        event(new CreateResourceEvent($resource, $user, $language));

        return TreeResource::make($resource);
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateResourceTreeRequest $request
     * @param Resource                  $resource
     * @param string                    $local
     * @return Renderable|AnonymousResourceCollection
     */
    public function update(
        UpdateResourceTreeRequest $request
    )
    {
        /**
         * check if sort
         */
        if ($request->sort) {
            Resource::setNewOrder($request->sort);
            collect($request->sort)->map(
                function ($res) {
                    $resource = Resource::where('id', $res['id'])->first();

                    $resource->update([
                        'parent_id' => optional($res)['parent_id']
                    ]);

                    if (!$resource->parent_id) {
                        $resource->base_id = $resource->id;
                    } elseif ($resource->parent) {
                        $resource->base_id = $resource->parent->base_id;
                    } else {
                        $resource->base_id = $resource->parent_id;
                    }
                    $resource->save();
                    $user = auth()->user();
                    $language = Language::where('iso', app()->getLocale())->firstORFail();
                    event(new UpdateResourceEvent($resource, $user, $language));
                }
            );
        }

        $tree = Resource::where('language', app()->getLocale())
            ->with(['createdby', 'children.createdby'])
            ->orderBy('sort', 'ASC')
            ->isParent()
            ->get([
                'id', 'base_id', 'title', 'parent_id', 'resource_id', 'language', 'isfolder',
                'published', 'hidden', 'hide_children_in_tree'
            ]);

        $children = Resource::where('language', app()->getLocale())
            ->with(['createdby'])
            ->whereIn('base_id', $tree->pluck('id')->toArray())
            ->orderBy('sort', 'ASC')
            ->get();

        return TreeResource::collection(
            $tree->merge($children)
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param string   $local
     * @param Resource $resource
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(
        Resource $resource
    )
    {

        $language = Language::where('iso', app()->getLocale())->firstOrFail();
        $user = auth()->user();
        $resource->deleted_by = $user->id;
        $resource->save();
        $resource->children()->get()->map(function ($child) use ($user) {
            $child->deleted_by = $user->id;
            $child->save();
            $child->delete();
        });
        if ($resource->delete()) {

            event(new DeleteResourceEvent($resource, $user, $language));
            /**
             * success response
             */
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_OK,
                    'message' => __('Resource deleted with success')
                ]
            ], Response::HTTP_OK);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('Resource couldn\'t be deleted')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }
}

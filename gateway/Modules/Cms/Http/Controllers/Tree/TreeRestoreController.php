<?php

namespace Modules\Cms\Http\Controllers\Tree;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Cms\Entities\Resource;

class TreeRestoreController extends Controller
{

    /**
     * Show the form for creating a new resource.
     * @param int $id
     * @return JsonResponse
     */
    public function restore(
        int $id
    )
    {
        if ($id) {
            $resource = Resource::where('id', $id)->withTrashed()->first();
            $resources = Resource::where('resource_id', $resource->id)->withTrashed()->get();
            $resources->map(function ($resource) {
                $resource->children()->restore();
                $resource->restore();
            });
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_OK,
                    'message' => __('Resource has been restored successfully.')
                ]
            ], Response::HTTP_OK);
        }

        Resource::onlyTrashed()->get()->map(function ($res) {
            $res->children()->restore();
            $res->restore();
        });
        return response()->json([
            'data' => [
                'status' => Response::HTTP_OK,
                'message' => __('Resources has been restored!')
            ]
        ], Response::HTTP_OK);

    }


}

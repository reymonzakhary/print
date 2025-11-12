<?php

namespace Modules\Cms\Http\Controllers\Resources;

use App\Models\Tenant\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Entities\ResourceGroup;
use Modules\Cms\Http\Requests\Resources\StoreResourceGroupRequest;
use Modules\Cms\Http\Requests\Resources\UpdateResourceGroupRequest;
use Modules\Cms\Transformers\Resources\ResourceGroupResource;

class ResourceGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return ResourceGroupResource::collection(
            ResourceGroup::with('resources')->get()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreResourceGroupRequest $request
     * @param string                    $local
     * @return ResourceGroupResource
     */
    public function store(
        StoreResourceGroupRequest $request
    )
    {
        return ResourceGroupResource::make(
            ResourceGroup::create($request->validated())
        )->additional([
            'message' => __('Resource Group has been created successfully.'),
            'status' => Response::HTTP_CREATED
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateResourceGroupRequest $request
     * @param string                     $local
     * @param int                        $id
     * @return JsonResponse|ResourceGroupResource
     */
    public function update(
        UpdateResourceGroupRequest $request,
        int                        $id
    )
    {
        // select resource group
        $resourceGroup = ResourceGroup::where('id', $id)->first();

        if (!$resourceGroup) {
            /**
             * error response
             */
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => __('Resource group couldn\'t be found.')
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        /**
         * check if has to be attached to resource or team
         */
        if ($request->attach) {
            $attach = "attachResourceGroupTo" . Str::ucfirst($request->attach);
            $rq = $request->attach . '_id';
            $this->{$attach}($request->$rq, $resourceGroup);
        } elseif ($request->detach) {
            $attach = "detachResourceGroupFrom" . Str::ucfirst($request->detach);
            $rq = $request->detach . '_id';
            $this->{$attach}($request->$rq, $resourceGroup);
        }

        /**
         * updated the resource group
         */
        if ($resourceGroup->update($request->validated())) {
            return ResourceGroupResource::make(
                $resourceGroup
            )->additional([
                'message' => __('Resource Group has been updated successfully.'),
                'status' => Response::HTTP_OK
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('Resource group couldn\'t be deleted')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     * @param string $local
     * @param int    $id
     * @return JsonResponse|ResourceGroupResource
     */
    public function destroy(
        int $id
    )
    {
        if (ResourceGroup::where('id', $id)->delete()) {
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_OK,
                    'message' => __('Resource group has been deleted successfully.')
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

    /**
     * @param int|null      $resource
     * @param ResourceGroup $resourceGroup
     */
    private function attachResourceGroupToResource(
        ?int          $resource,
        ResourceGroup $resourceGroup
    ): void
    {
        if ($resource) {
            Resource::where('resource_id', $resource)->get()
                ->map(fn($res) => $res->groups()->syncWithoutDetaching($resourceGroup));
        }
    }

    /**
     * @param int|null      $resource
     * @param ResourceGroup $resourceGroup
     */
    private function detachResourceGroupFromResource(
        ?int          $resource,
        ResourceGroup $resourceGroup
    ): void
    {
        if ($resource) {
            Resource::where('resource_id', $resource)->get()
                ->map(fn($res) => $res->groups()->detach($resourceGroup));
        }
    }


    /**
     * @param int|null      $team
     * @param ResourceGroup $resourceGroup
     */
    private function attachResourceGroupToTeam(
        ?int          $team,
        ResourceGroup $resourceGroup
    ): void
    {
        if ($team) {
            Team::where('id', $team)->first()->resourceGroups()->syncWithoutDetaching($resourceGroup);
        }
    }

    /**
     * @param int|null      $team
     * @param ResourceGroup $resourceGroup
     */
    private function detachResourceGroupFromTeam(
        ?int          $team,
        ResourceGroup $resourceGroup
    ): void
    {
        if ($team) {
            Team::where('id', $team)->first()->resourceGroups()->detach($resourceGroup);
        }
    }


}

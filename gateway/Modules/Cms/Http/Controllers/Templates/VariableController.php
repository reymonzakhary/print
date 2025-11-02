<?php

namespace Modules\Cms\Http\Controllers\Templates;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Cms\Entities\Variable;
use Modules\Cms\Http\Requests\Variables\CreateVariableRequest;
use Modules\Cms\Http\Requests\Variables\UpdateVariableRequest;
use Modules\Cms\Transformers\Resource\VariableResource;

class VariableController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return VariableResource::collection(
            Variable::get()
        )->additional([
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param CreateVariableRequest $request
     * @return VariableResource
     */
    public function store(
        CreateVariableRequest $request
    )
    {
        return VariableResource::make(
            Variable::create($request->validated())
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Show the specified resource.
     * @param Variable $variable
     * @return Application|Factory|View|\Laravel\Lumen\Application|VariableResource
     */
    public function show(
        Variable $variable
    )
    {
        return VariableResource::make($variable)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateVariableRequest $request
     * @param Variable              $variable
     * @return JsonResponse|VariableResource
     */
    public function update(
        UpdateVariableRequest $request,
        Variable              $variable
    )
    {
        if ($variable->update($request->validated())) {
            return VariableResource::make($variable)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('Variable couldn\'t be updated')
            ]
        ], Response::HTTP_BAD_REQUEST);

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(
        Variable $variable
    )
    {
        if ($variable->delete()) {
            /**
             * error response
             */
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_OK,
                    'message' => __('Variable Delete')
                ]
            ], Response::HTTP_OK);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('Variable couldn\'t be updated')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }
}

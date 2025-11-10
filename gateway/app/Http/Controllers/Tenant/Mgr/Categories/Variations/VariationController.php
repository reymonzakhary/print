<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories\Variations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Variations\StoreVariationRequest;
use App\Http\Requests\Variations\UpdateVariationRequest;
use App\Http\Resources\Variations\VariationResource;
use App\Models\Tenant\Variation;
use App\Repositories\VariationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VariationController extends Controller
{
    /**
     * @var VariationRepository
     */
    protected VariationRepository $variation;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * UserController constructor.
     * @param Request   $request
     * @param Variation $variation
     */
    public function __construct(
        Request   $request,
        Variation $variation
    )
    {
        $this->variation = new VariationRepository($variation);

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
    }

    /**
     * Obtain paginated variation
     * @return mixed
     */
    public function index()
    {
        /** @var variation obtain  $variation */
        $variation = $this->variation->all($this->per_page);

        /**
         * check if we have variation
         */
        if ($variation->items()) {
            return VariationResource::collection($variation)->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('variations.no_variation_available'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * @param StoreVariationRequest $request
     * @return VariationResource|JsonResponse
     */
    public function store(
        StoreVariationRequest $request
    )
    {
        return VariationResource::make(
            $this->variation->create(
                $request->validated()
            )
        )
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => null
            ]);
    }

    /**
     * @param UpdateVariationRequest $request
     * @param int                    $id
     * @return JsonResponse|VariationResource|JsonResponse
     */
    public function update(
        UpdateVariationRequest $request,
        int                    $id
    )
    {
        if (
            $this->variation->update(
                $id,
                $request->validated()
            )
        ) {
            return VariationResource::make($this->variation->show($id))
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('variations.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param int $id
     * @return JsonResponse|JsonResponse
     */
    public function destroy(
        int $id
    )
    {
        if (
            $this->variation->delete(
                $id
            )
        ) {
            /**
             * error response
             */
            return response()->json([
                'data' => [
                    'message' => __('variations.variation_removed')
                ]
            ], Response::HTTP_ACCEPTED);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('variations.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

}

<?php

namespace App\Http\Controllers\Tenant\Mgr\Stocks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\UpdateStockRequest;
use App\Http\Requests\Stocks\StoreStockRequest;
use App\Http\Resources\Stocks\StockResource;
use App\Models\Tenants\Stock;
use App\Repositories\StockRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class StockController
 * @package App\Http\Controllers\Tenant\Mgr\Stocks
 */
class StockController extends Controller
{

    /**
     * @var array|mixed
     */
    public array $hide = [];

    /**
     * @var StockRepository
     */
    protected StockRepository $stock;

    /**
     * UserController constructor.
     * @param Request $request
     * @param Stock   $stock
     */
    public function __construct(
        Request $request,
        Stock   $stock
    )
    {
        $this->hide = $request->get('hide') ?? [];
        $this->stock = new StockRepository($stock);

    }

    /**
     * Obtain paginated status
     * @return mixed
     */
    public function index()
    {
        /**
         * check if we have status
         */
        if ($stock = $this->stock->all()) {
            return StockResource::collection($stock)
                ->hide($this->hide)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('stock.no_status_available'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * @param int $code
     * @return StockResource|JsonResponse
     */
    public function show(
        int $code
    )
    {
        if ($stock = $this->stock->show($code)) {
            return StockResource::make($stock)
                ->hide($this->hide)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('stock.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);

    }


    /**
     * @param StoreStockRequest $request
     * @return StockResource|JsonResponse
     */
    public function store(
        StoreStockRequest $request
    )
    {
        return StockResource::make(
            $this->stock->create(
                $request->validated()
            )
        )
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => null
            ]);
    }

    /**
     * @param UpdateStockRequest $request
     * @param int                $id
     * @return StockResource|JsonResponse
     */
    public function update(
        UpdateStockRequest $request,
        int                $id
    )
    {
        if (
            $this->stock->update(
                $id,
                $request->validated()
            )
        ) {
            return StockResource::make($this->stock->show($id))
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => __('stock.updated'),
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('stock.not_found'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(
        int $id
    )
    {
        if (
            $this->stock->delete(
                $id
            )
        ) {
            /**
             * error response
             */
            return response()->json([
                'data' => [
                    'message' => __('stock.service_removed'),
                ]
            ], Response::HTTP_ACCEPTED);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('stock.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }
}

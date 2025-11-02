<?php

namespace App\Http\Controllers\Tenant\Mgr\Discounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Discounts\StoreDiscountRequest;
use App\Http\Requests\Discounts\UpdateDiscountRequest;
use App\Http\Resources\Discounts\DiscountResource;
use App\Models\Tenants\Discount;
use App\Repositories\DiscountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Discount
 */
class DiscountController extends Controller
{
    /**
     * @var DiscountRepository
     */
    protected DiscountRepository $discount;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * UserController constructor.
     * @param Request  $request
     * @param Discount $discount
     */
    public function __construct(
        Request  $request,
        Discount $discount
    )
    {
        $this->discount = new DiscountRepository($discount);
        /**
         * default hidden field
         */
        $this->hide = [

        ];

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
    }

    /**
     * list discounts
     *  
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"data": [
     * 		{
     * 			"id": 1,
     * 			"type": "fixed",
     * 			"value": 15,
     * 			"display_value": "€ 0,15",
     * 			"created_at": "2024-05-16T13:08:45.000000Z",
     * 			"updated_at": "2024-05-16T13:08:45.000000Z"
     * 		}
     * 	],
     * 	"status": 200,
     * 	"message": null
     * }
     * 
     * @return mixed
     */
    public function index()
    {
        /**
         * check if we have discount
         */
        if ($discount = $this->discount->all()) {
            return DiscountResource::collection($discount)->hide(
                $this->hide
            )->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('discounts.no_discount_available'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * show discount 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"type": "fixed",
     * 		"value": 15,
     * 		"display_value": "€ 0,15",
     * 		"created_at": "2024-05-16T13:08:45.000000Z",
     * 		"updated_at": "2024-05-16T13:08:45.000000Z"
     * 	},
     * 	"status": 200,
     * 	"message": null
     * }
     * 
     * obtain single discount
     * @param Discount $discount
     * @return DiscountResource
     */
    public function show(
        Discount $discount
    )
    {
        return DiscountResource::make($discount)
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * store discount
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 201
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"type": "fixed",
     * 		"value": 15,
     * 		"display_value": "€ 0,15",
     * 		"created_at": "2024-05-16T13:08:45.000000Z",
     * 		"updated_at": "2024-05-16T13:08:45.000000Z"
     * 	},
     * 	"status": 201,
     * 	"message": null
     * }
     * 
     * @response 422
     * {
     * 	"message": "The type field is required. (and 2 more errors)",
     * 	"errors": {
     * 		"type": [
     * 			"The type field is required."
     * 		],
     * 		"value": [
     * 			"The value field is required."
     * 		],
     * 		"ctx_id": [
     * 			"The ctx id field is required."
     * 		]
     * 	}
     * }
     * 
     * @param StoreDiscountRequest $request
     * @return DiscountResource|JsonResponse
     */
    public function store(
        StoreDiscountRequest $request
    )
    {
        return DiscountResource::make(
            $this->discount->create(
                $request->validated()
            )
        )
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => null
            ]);
    }

    /**
     * update discount 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"message": "Discount has been updated successfully.",
     * 	"status": 200
     * }
     * 
     * @response 422
     * {
     * 	"message": "The type field is required. (and 2 more errors)",
     * 	"errors": {
     * 		"type": [
     * 			"The type field is required."
     * 		],
     * 		"value": [
     * 			"The value field is required."
     * 		],
     * 		"ctx_id": [
     * 			"The ctx id field is required."
     * 		]
     * 	}
     * }
     * 
     * @response 400
     * {
     * 	"message": "We could'not handel your request, please try again later",
     * 	"status": 400
     * }
     * 
     * @param UpdateDiscountRequest $request
     * @param int                   $id
     * @return DiscountResource|JsonResponse
     */
    public function update(
        UpdateDiscountRequest $request,
        int                   $id
    )
    {
        if (
            $this->discount->update(
                $id,
                $request->validated()
            )
        ) {
            return response()->json([
                'message' => __('discounts.discount_updated'),
                'status' => Response::HTTP_OK

            ], Response::HTTP_OK);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('discounts.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * delete discount
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"message": "discount has been removed.",
     * 	"status": 200
     * }
     * 
     * @response 400
     * {
     * 	"message": "We could'not handel your request, please try again later",
     * 	"status": 400
     * }
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(
        int $id
    )
    {
        if (
            $this->discount->delete(
                $id
            )
        ) {
            /**
             * error response
             */
            return response()->json([
                'message' => __('discounts.discount_removed'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('discounts.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

}

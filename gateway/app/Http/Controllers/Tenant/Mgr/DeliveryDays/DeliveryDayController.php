<?php

namespace App\Http\Controllers\Tenant\Mgr\DeliveryDays;

use App\Events\DeliveryDays\CreateDeliveryDayEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeliveryDay\DeliveryDaysRequest;
use App\Http\Resources\DeliveryDay\DeliveryDaysResource;
use App\Models\Tenant\DeliveryDay;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Delivery Days
 */
class DeliveryDayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    /**
     * List Delivery Days
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
	 * "data": [
	 * 	{
	 * 		"label": "test",
	 * 		"slug": "test",
	 * 		"iso": "en",
	 * 		"days": 5,
	 * 		"mode": "fixed",
	 * 		"price": null
	 * 	},
     *  ]
     * }
     *
     */
    public function index()
    {
        return DeliveryDaysResource::collection(DeliveryDay::all());
    }

    /**
     * Store Delivery Day
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 201
     * {
     * 	"data": {
     * 		"label": "test",
     * 		"slug": "test",
     * 		"iso": "en",
     * 		"days": 5,
     * 		"mode": "fixed",
     * 		"price": null
     * 	},
     * 	"message": "Delivery day has been create Successfully",
     * 	"status": 201
     * }
     *
     * @response 422
     * {
     * 	"message": "The label field is required. (and 2 more errors)",
     * 	"errors": {
     * 		"label": [
     * 			"The label field is required."
     * 		],
     * 		"days": [
     * 			"The days field is required."
     * 		],
     * 		"mode": [
     * 			"The mode field is required."
     * 		]
     * 	}
     * }
     *
     * @param Request $request
     * @return Response
     */
    public function store(
        DeliveryDaysRequest $request
    )
    {
        if ($deliveryDay = DeliveryDay::Create($request->validated())) {
            event(new CreateDeliveryDayEvent($deliveryDay, $request->iso));
            return DeliveryDaysResource::make($deliveryDay)->additional([
                "message" => _('Delivery day has been create Successfully'),
                "status" => Response::HTTP_CREATED
            ]);
        }
    }

    /**
     * Show Delivery Day
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"data": {
     * 		"label": "test",
     * 		"slug": "test",
     * 		"iso": "en",
     * 		"days": 5,
     * 		"mode": "fixed",
     * 		"price": null
     * 	}
     * }
     *
     * @param \App\Models\DeliveryDay $deliveryDay
     * @return Response
     */
    public function show(DeliveryDay $deliveryDay)
    {
        if ($deliveryDay) {
            return DeliveryDaysResource::make($deliveryDay);
        } else {
            return response()->json([
                'message' => _("Don't have this day")
            ]);
        }
    }


    /**
     * Update DeliveryDay
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"message": "Delivery day Update Successfully.",
     * 	"status": 200
     * }
     *
     * @response 404
     * {
     * 	"message": "We can't handle this Request",
     * 	"status": 404
     * }
     *
     * @param Request                 $request
     * @param \App\Models\DeliveryDay $deliveryDay
     * @return Response
     */
    public function update(
        DeliveryDaysRequest $request,
        DeliveryDay $deliveryDay
    )
    {
        if ($deliveryDay) {
            if ($deliveryDay->update($request->validated())) ;
            {
                return response()->json([
                    'message' => __('Delivery day Update Successfully.'),
                    'status' => Response::HTTP_OK
                ]);
            }
        } else {
            return response()->json([
                'message' => __("Don't have this day"),
                'status' => Response::HTTP_NOT_FOUND
            ]);
        }

        return response()->json([
            'message' => __("We can't handle this Request"),
            'status' => Response::HTTP_NOT_FOUND
        ]);
    }

    /**
     * Delete Delivery Day
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"message": "Delivery Day has been Deleted Successfully. ",
     * 	"status": 404
     * }
     *
     * @response 404
     * {
     * 	"message": "Don't have this day",
     * 	"status": 404
     * }
     *
     * @param \App\Models\DeliveryDay $deliveryDay
     * @return Response
     */
    public function destroy($delivery)
    {


        if (DeliveryDay::where('slug', $delivery)->delete()) {
            return response()->json([
                'message' => __("Delivery Day has been Deleted Successfully. "),
                'status' => Response::HTTP_NOT_FOUND
            ]);
        }

        return response()->json([
            'message' => __("Don't have this day"),
            'status' => Response::HTTP_NOT_FOUND
        ]);
    }
}

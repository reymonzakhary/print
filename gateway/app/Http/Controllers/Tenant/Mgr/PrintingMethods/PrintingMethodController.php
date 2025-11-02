<?php

namespace App\Http\Controllers\Tenant\Mgr\PrintingMethods;

use App\Events\PrintingMethods\CreatePrintingMethodEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\PrintingMethods\PrintingMethodStoreRequest;
use App\Http\Requests\PrintingMethods\PrintingMethodUpdateRequest;
use App\Http\Resources\PrintingMethods\PrintingMethodResource;
use App\Models\Tenants\PrintingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Print Method
 */
class PrintingMethodController extends Controller
{

    /**
     * default hiding field from response
     */
    protected array $hide;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * List of printing methods
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
	 * "data": [
	 * 	{
	 * 		"id": 1,
	 * 		"name": "test",
	 * 		"slug": "test",
	 * 		"iso": "en",
	 * 		"sort": 1,
	 * 		"row_id": 1
	 * 	}
	 * ],
     * "links":{},
     * "meta":{},
     * "status":200,
     * "message": null
     * }
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return PrintingMethodResource::collection(
            PrintingMethod::where('iso', app()->getLocale())
                ->ordered(request()->order_dir ?? 'asc')
                ->paginate(
                    request()->get('per_page') ?? $this->per_page
                )
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }


    /**
     * show printing Method 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"name": "test",
     * 		"slug": "test",
     * 		"iso": "en",
     * 		"sort": 1,
     * 		"row_id": 1
     * 	},
     * 	"message": null,
     * 	"status": 200
     * }
     * 
     * @param PrintingMethod $printingMethod
     * @return PrintingMethodResource
     */
    public function show(
        PrintingMethod $printingMethod
    )
    {
        return PrintingMethodResource::make(
            $printingMethod
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * store printing method
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 201
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"name": "test",
     * 		"slug": "test",
     * 		"iso": "en",
     * 		"sort": 1,
     * 		"row_id": 1
     * 	},
     * 	"message": null,
     * 	"status": 201
     * }
     * 
     * @response 422
     * {
     * 	"message": "The name field is required.",
     * 	"errors": {
     * 		"name": [
     * 			"The name field is required."
     * 		]
     * 	}
     * }
     * 
     * @return PrintingMethodResource
     */
    public function store(
        PrintingMethodStoreRequest $request
    )
    {
        $printingMethod = PrintingMethod::create($request->validated());
        event(new CreatePrintingMethodEvent($printingMethod, $request->iso));

        return PrintingMethodResource::make(
            $printingMethod
        )->additional([
            'message' => null,
            'status' => Response::HTTP_CREATED
        ]);

    }


    /**
     * update printing method 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"name": "lol",
     * 		"slug": "lol",
     * 		"iso": "en",
     * 		"sort": 1,
     * 		"row_id": 1
     * 	},
     * 	"message": "Printing method has been updated successfully.",
     * 	"status": 200
     * }
     * 
     * @response 400
     * {
     *  "message": "We couldn\'t update this printing method, please try again later.",
     *  "status": 400
     * }
     * 
     * @response 422
     * {
     * 	"message": "The name field is required.",
     * 	"errors": {
     * 		"name": [
     * 			"The name field is required."
     * 		]
     * 	}
     * }
     * 
     * @param PrintingMethodUpdateRequest $request
     * @param PrintingMethod              $printingMethod
     * @return JsonResponse|PrintingMethodResource
     */
    public function update(
        PrintingMethodUpdateRequest $request,
        PrintingMethod              $printingMethod
    )
    {
        if ($printingMethod->update($request->validated())) {
            return PrintingMethodResource::make(
                PrintingMethod::where('id', $printingMethod->id)->first()
            )->additional([
                'message' => __('Printing method has been updated successfully.'),
                'status' => Response::HTTP_OK
            ]);
        } else {
            return response()->json([
                'message' => __('We couldn\'t update this printing method, please try again later.'),
                'status' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * delete printing method
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"message": "Printing method has been deleted successfully.",
     * 	"status": 200
     * }
     *  
     * @response 400
     * {
     * 	"message": "We couldn't delete this printing method, please try again later.",
     * 	"status": 400
     * }
     * 
     * @param PrintingMethod $printingMethod
     * @return JsonResponse
     */
    public function delete(
        PrintingMethod $printingMethod
    )
    {
        if (PrintingMethod::where('row_id', $printingMethod->row_id)->delete()) {
            return response()->json([
                'message' => __('Printing method has been deleted successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        return response()->json([
            'message' => __('We couldn\'t delete this printing method, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

}

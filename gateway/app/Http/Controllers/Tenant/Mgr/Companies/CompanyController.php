<?php

namespace App\Http\Controllers\Tenant\Mgr\Companies;

use App\Enums\MemberType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Companies\CompanyStoreRequest;
use App\Http\Requests\Companies\CompanyUpdateRequest;
use App\Http\Resources\Companies\CompanyResource;
use App\Models\Tenant\Company;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Companies
 *
 * @subgroup Tenant Companies
 * @subgroupDescription
 */
class CompanyController extends Controller
{

    public $hide = [];

    /**
     * List Companies
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
	 * 		"name": "test company",
     * 		"description": "company description",
     * 		"coc": "123456",
     * 		"tax_nr": "15646",
     * 		"email": "test@gmail.com",
     * 		"url": "https://test.com",
	 * 		"created_at": "2024-05-01T11:40:06.000000Z",
	 * 		"updated_at": "2024-05-12T08:05:58.000000Z"
	 * 	}
	 * ],
     * "links":{},
     * "meta":{},
     * "status":200,
     * "message": null
     * }
     *
     * @return CompanyResource
     */
    public function index()
    {
        return CompanyResource::collection(Company::paginate(10));
    }

    public function show(
        Company $company,
    ): CompanyResource
    {
        return CompanyResource::make($company)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * store Company
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 201
     * {
     *    "data": {
     *        "id": 2,
     *        "name": "test company",
     *        "description": "company description",
     *        "coc": "123456",
     *        "tax_nr": "15646",
     *        "email": "test@gmail.com",
     *        "url": "https://test.com",
     *        "addresses": [],
     *        "created_at": "2024-05-16T11:50:36.000000Z",
     *        "updated_at": "2024-05-16T11:50:36.000000Z"
     *    },
     *    "status": 201,
     *    "message": null
     * }
     *
     * @response 422
     * {
     *    "message": "The name field is required.",
     *    "errors": {
     *        "name": [
     *            "The name field is required."
     *        ]
     *    }
     * }
     * @param CompanyStoreRequest $request
     * @return CompanyResource
     */
    public function store(
        CompanyStoreRequest $request
    )
    {
        return CompanyResource::make(Company::create($request->validated()))->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => null
            ]);
    }

    /**
     * Update Company
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"data": {
     * 		"id": 2,
     * 		"name": "test company",
     * 		"description": "company description",
     * 		"coc": "123456",
     * 		"tax_nr": "15646",
     * 		"email": "test@gmail.com",
     * 		"url": "https://test.com",
     * 		"addresses": [],
     * 		"created_at": "2024-05-16T11:50:36.000000Z",
     * 		"updated_at": "2024-05-16T11:50:36.000000Z"
     * 	},
     * 	"status": 201,
     * 	"message": null
     * }
     *
     * @response 422
     * {
     * {
     * 	"message": "The name field is required.",
     * 	"errors": {
     * 		"name": [
     * 			"The name field is required."
     * 		]
     * 	}
     * }
     * }
     *
     * @response 400
     * {
     * 	"message": "We could'not handel your request, please try again later",
     * 	"status": 400
     * }
     */
    /**
     * @param CompanyUpdateRequest $request
     * @param Company $company
     * @return CompanyResource|JsonResponse
     */
    public function update(
        CompanyUpdateRequest $request,
        Company                  $company
    )
    {

        if ($company->update($request->validated())) {
            return CompanyResource::make($company)
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
            'message' => __('companies.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Delete Company
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param Company $company
     * @return JsonResponse
     */
    public function destroy(
        Company $company
    ): JsonResponse
    {
        // change type of user to be individual to ensure data integrity
        $company->users()->update(['type' => MemberType::INDIVIDUAL->value]);

        if ($company->teams()->count() > 0) {
            return response()->json([
                'message' => __('Company has existing teams, you must delete them first before you can delete the company.'),
                'status' => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        if (!$company->delete()) {
            return response()->json([
                'message' => __('We could\'not handel your request, please try again later'),
                'status' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'data' => [
                'message' => __('company has been removed.')
            ]
        ], Response::HTTP_OK);
    }
}

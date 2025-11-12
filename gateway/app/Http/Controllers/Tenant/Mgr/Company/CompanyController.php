<?php

namespace App\Http\Controllers\Tenant\Mgr\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserCompanyUpdateRequest;
use App\Http\Resources\Companies\CompanyResource;
use App\Models\Tenant\Company;
use App\Models\Tenant\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Users
 *
 * @subgroup Tenant User Company
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
	 * 		"name": "CHD",
	 * 		"description": "company description",
	 * 		"coc": "123456",
	 * 		"tax_nr": "123456",
	 * 		"email": "company@gmail.com",
	 * 		"url": "https://www.company.com",
	 * 		"addresses": [],
	 * 		"created_at": "2024-05-01T11:40:06.000000Z",
	 * 		"updated_at": "2024-05-01T11:40:06.000000Z"
	 * 	}
	 * ],
     * "links":{},
     * "meta":{},
     * }
     *
     * @return CompanyResource|JsonResponse
     */
    public function index()
    {
        $company = Company::main()->first();
        return CompanyResource::make($company)
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Update Company
     *
     * @response 200
     * {
     *    "data": {
     *        "id": 1,
     *        "name": "company",
     *        "description": "company description",
     *        "coc": "123456",
     *        "tax_nr": "123456",
     *        "email": "company@gmail.com",
     *        "url": "https://www.company.com",
     *        "addresses": [],
     *        "created_at": "2024-05-01T11:40:06.000000Z",
     *        "updated_at": "2024-05-12T08:05:58.000000Z"
     *    },
     *    "status": 200,
     *    "message": null
     * }
     *
     * @param UserCompanyUpdateRequest $request
     * @return CompanyResource|JsonResponse
     */
    public function update(
        UserCompanyUpdateRequest $request,
    )
    {
        $company = Company::main()->first();
        if (!$company) {
            return response()->json([
                "message" => __("Company not found!"),
                "status" => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
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

}

<?php

namespace App\Http\Controllers\Tenant\Mgr\Users\Companies;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserCompanyStoreRequest;
use App\Http\Requests\Users\UserCompanyUpdateRequest;
use App\Http\Resources\Companies\CompanyResource;
use App\Models\Tenants\Company;
use App\Models\Tenants\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CompanyController extends Controller
{

    public $hide = [];

    /**
     * Display a listing of the resource.
     *
     * @param User $user
     * @return Response
     */
    public function index(
        User $user
    )
    {
        return CompanyResource::collection($user->companies()->paginate(10));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param UserCompanyStoreRequest $request
     * @param User                    $user
     * @return CompanyResource|void
     */
    public function store(
        UserCompanyStoreRequest $request,
        User                    $user
    )
    {
        return CompanyResource::make($user->companies()->create($request->validated()))->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => null
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserCompanyUpdateRequest $request
     * @param User                     $user
     * @param int                      $id
     * @return CompanyResource|JsonResponse|void
     */
    public function update(
        UserCompanyUpdateRequest $request,
        User                     $user,
        int                      $id
    )
    {

        if ($user->companies()->where('companies.id', $id)->exists()) {
            $company = Company::where('id', $id)->first();
            if ($company->update($request->validated())) {
                return CompanyResource::make($company)
                    ->hide($this->hide)
                    ->additional([
                        'status' => Response::HTTP_OK,
                        'message' => null
                    ]);
            }
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
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @param int  $id
     * @return CompanyResource|JsonResponse
     */
    public function destroy(
        User $user,
        int  $id
    )
    {
        if ($user->companies()->where('companies.id', $id)->exists()) {
            Company::where('id', $id)->delete();
            return response()->json([
                'data' => [
                    'message' => __('companies.company_removed')
                ]
            ], Response::HTTP_OK);
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

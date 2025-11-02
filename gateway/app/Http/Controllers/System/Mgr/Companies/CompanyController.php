<?php

namespace App\Http\Controllers\System\Mgr\Companies;

use App\Http\Controllers\Controller;
use App\Http\Resources\Companies\CompanyResource;
use App\Models\Company;
use Symfony\Component\HttpFoundation\Response;

class CompanyController extends Controller
{

    /**
     * @return CompanyResource
     */
    public function company()
    {
        return CompanyResource::make(
            auth()->user()->company??[]
        )->hide(['addresses'])
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * @return CompanyResource
     */
    public function index()
    {
        return CompanyResource::collection(
            Company::where('user_id', auth()->user()->id)->get()??[]
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\V2\Mgr\Companies;

use App\Http\Controllers\Controller;
use App\Http\Resources\Companies\CompanyResource;
use App\Http\Resources\Companies\CompanyResourceCollection;
use App\Models\Company;
use Symfony\Component\HttpFoundation\Response;

class CompanyController extends Controller
{

    /**
     * @return CompanyResource
     */
    public function company(): CompanyResource
    {
        return CompanyResource::make(
            auth()->user()->company??[]
        )->hide(['addresses'])
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

   
    public function index(): CompanyResourceCollection
    {
        return CompanyResource::collection(
            Company::where('user_id', auth()->user()->id)->get()??[]
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

}

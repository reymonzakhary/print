<?php

namespace App\Http\Controllers\System\Mgr\Countries;

use App\Http\Controllers\Controller;
use App\Http\Resources\Country\CountryResource;
use App\Models\Country;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CountryController extends Controller
{
    /**
     * @return AnonymousResourceCollection|mixed
     */
    public function __invoke()
    {
        return CountryResource::collection(
            Country::all()
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }
}

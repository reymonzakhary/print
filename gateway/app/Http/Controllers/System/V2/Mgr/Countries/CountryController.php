<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\V2\Mgr\Countries;

use App\Http\Controllers\Controller;
use App\Http\Resources\Country\CountryResource;
use App\Models\Country;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class CountryController extends Controller
{
    /**
     * @return AnonymousResourceCollection|mixed
     */
    public function __invoke(): mixed
    {
        return CountryResource::collection(
            Country::query()->get()
        )
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }
}

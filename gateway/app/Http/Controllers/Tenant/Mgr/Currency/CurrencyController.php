<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Currency;

use App\Http\Controllers\Controller;
use App\Http\Resources\Currency\CurrencyResource;
use App\Models\Country;
use App\Plugins\Moneys;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class CurrencyController extends Controller
{
    /**
     * @return AnonymousResourceCollection|mixed
     */
    public function __invoke(): mixed
    {


        return CurrencyResource::collection(Moneys::getCurrencyByLang())
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);

    }
}

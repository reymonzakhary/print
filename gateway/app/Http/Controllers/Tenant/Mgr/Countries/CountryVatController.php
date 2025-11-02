<?php

namespace App\Http\Controllers\Tenant\Mgr\Countries;

use App\Http\Controllers\Controller;
use App\Http\Requests\Countries\StoreCountryTaxRequest;
use App\Http\Resources\Countries\VatResource;
use App\Models\Country;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CountryVatController extends Controller
{
    /**
     * Retrieves a collection of VAT resources associated with the provided country.
     *
     * @param Country $country The country for which to retrieve VAT resources
     * @return AnonymousResourceCollection Collection of VAT resources with additional data including message and status
     */
    public function index(
        Country $country
    ): AnonymousResourceCollection
    {
        return VatResource::collection($country->taxes()->get())
            ->additional([
                'message' => '',
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Store a new country tax record.
     *
     * @param StoreCountryTaxRequest $request The request containing the tax data.
     * @param Country $country The country to associate the tax with.
     * @return VatResource The resource representing the created VAT entry.
     */
    public function store(
        StoreCountryTaxRequest $request,
        Country $country
    ): VatResource
    {
            return VatResource::make($country->taxes()->create($request->validated()))
                ->additional([
                    'message' => __('Country VAT created successfully.'),
                    'status' => Response::HTTP_CREATED

                ]);

    }
}

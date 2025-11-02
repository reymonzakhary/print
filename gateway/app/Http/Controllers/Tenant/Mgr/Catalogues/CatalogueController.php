<?php

namespace App\Http\Controllers\Tenant\Mgr\Catalogues;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogues\CatalogueStoreRequest;
use App\Http\Requests\Catalogues\CatalogueUpdateRequest;
use App\Http\Resources\Catalogues\CatalogueResource;
use App\Models\Tenants\Catalogue;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Catalogues
 */
class CatalogueController extends Controller
{
    /**
     * List catalogues
     *
     * @header   Origin http://{sub_domin}.prindustry.test
     * @header   Referer http://{sub_domin}.prindustry.test
     * @header   Authorization Bearer token
     *
     * @response 200
     * {
     *    "data": [
     *        {
     *            "id": 1,
     *            "supplier": "test supplier",
     *            "art_nr": "55",
     *            "material": "test material",
     *            "material_link": "01ARZ3NDEKTSV4RRFFQ69G5FAV",
     *            "grs": "50 gr",
     *            "grs_link": "01ARZ3NDEKTSV4RRFFQ69G5FA8",
     *            "price": 0,
     *            "display_price": "€ 0,00",
     *            "ean": null,
     *            "calc_type": "lol",
     *            "created_at": "2024-05-01 11:39:44",
     *            "updated_at": "2024-05-01 11:39:44"
     *        }
     *    ],
     *    "message": null,
     *    "status": 200
     * }
     *
     *
     * @return CatalogueResource
     * @throws ValidationException
     */
    public function index()
    {
        return CatalogueResource::collection(
            Catalogue::query()->obtain()->get()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Store Catalogue
     *
     * @header   Origin http://{sub_domin}.prindustry.test
     * @header   Referer http://{sub_domin}.prindustry.test
     * @header   Authorization Bearer token
     *
     * @response 201
     * {
     *    "data": {
     *        "id": 1,
     *        "supplier": "test supplier",
     *        "art_nr": "55",
     *        "material": "test material",
     *        "material_link": "01ARZ3NDEKTSV4RRFFQ69G5FAV",
     *        "grs": "50 gr",
     *        "grs_link": "01ARZ3NDEKTSV4RRFFQ69G5FA8",
     *        "price": 0,
     *        "display_price": "€ 0,00",
     *        "ean": null,
     *        "calc_type": "lol",
     *        "created_at": "2024-05-01 11:39:44",
     *        "updated_at": "2024-05-01 11:39:44"
     *    },
     *    "message": "Catalogue item has been created successfully.",
     *    "status": 201
     * }
     *
     * @response 422
     * {
     *    "message": "The supplier field is required. (and 4 more errors)",
     *    "errors": {
     *        "supplier": [
     *            "The supplier field is required."
     *        ],
     *        "art_nr": [
     *            "The art nr field is required."
     *        ],
     *        "material": [
     *            "The material field is required."
     *        ],
     *        "material_link": [
     *            "The material link field is required."
     *        ],
     *        "calc_type": [
     *            "The calc type field is required."
     *        ]
     *    }
     * }
     *
     * @param CatalogueStoreRequest $request
     * @return CatalogueResource
     * @throws ValidationException
     */
    public function store(
        CatalogueStoreRequest $request
    ): CatalogueResource
    {

        return CatalogueResource::make(
            Catalogue::query()->obtainCreate($request->validated())->first()
        )->additional([
            'message' => __('Catalogue item has been created successfully.'),
            'status' => Response::HTTP_CREATED
        ]);
    }

    /**
     * Update Catalogue
     *
     * @header   Origin http://{sub_domin}.prindustry.test
     * @header   Referer http://{sub_domin}.prindustry.test
     * @header   Authorization Bearer token
     *
     * @response 200
     * {
     *    "data": {
     *        "id": 1,
     *        "supplier": "test supplier",
     *        "art_nr": "55",
     *        "material": "test material",
     *        "material_link": "01ARZ3NDEKTSV4RRFFQ69G5FAV",
     *        "grs": "50 gr",
     *        "grs_link": "01ARZ3NDEKTSV4RRFFQ69G5FA8",
     *        "price": 0,
     *        "display_price": "€ 0,00",
     *        "ean": null,
     *        "calc_type": "lol",
     *        "created_at": "2024-05-01 11:39:44",
     *        "updated_at": "2024-05-01 11:39:44"
     *    },
     *    "message": "Catalogue has been updated successfully.",
     *    "status": 200
     * }
     *
     * @response 422
     * {
     *    "message": "The art nr field is required. (and 3 more errors)",
     *    "errors": {
     *        "art_nr": [
     *            "The art nr field is required."
     *        ],
     *        "material": [
     *            "The material field is required."
     *        ],
     *        "material_link": [
     *            "The material link field is required."
     *        ],
     *        "calc_type": [
     *            "The calc type field is required."
     *        ]
     *    }
     * }
     *
     * @response 400
     * {
     *    "message": "We couldn't update the catalogue you have requested.",
     *    "status": 400
     * }
     *
     * @param CatalogueUpdateRequest $request
     * @param string                 $catalogue
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(
        CatalogueUpdateRequest $request,
        string $catalogue
    ): JsonResponse
    {
        if(
            Catalogue::query()
                ->obtainUpdate($catalogue, $request->validated())
                ->update()
        ) {

            return response()->json([
                'message' => __('Catalogue has been updated successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('We couldn\'t handle your request, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);

    }

    /**
     * Delete Catalogue
     *
     * @header   Origin http://{sub_domin}.prindustry.test
     * @header   Referer http://{sub_domin}.prindustry.test
     * @header   Authorization Bearer token
     *
     *
     * @response 400
     * {
     *    "message": "We could'not handel your request, please try again later",
     *    "status": 400
     * }
     *
     * @response 200
     * {
     *    "message": "Catalogue has been deleted successfully.",
     *    "status": 200
     * }
     *
     * @param string $catalogue
     * @return JsonResponse
     * @throws ValidationException
     */
    public function destroy(
        string $catalogue
    ): JsonResponse
    {
        if(Catalogue::query()->obtainDelete($catalogue)->delete()) {

            return response()->json([
                'message' => __('Catalogue has been deleted successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        return response()->json([
            'message' => __('We couldn\'t delete the requested catalogue, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }
}


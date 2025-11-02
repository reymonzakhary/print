<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories;

use App\Events\Tenant\Categories\CategoryImportExcel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryImportController extends Controller
{

    /**
     * @OA\Post (
     *     tags={"Category Import"},
     *     path="/api/v1/mgr/categories/{slug}/products/import",
     *     summary="Import Categories form excal",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(
     *          @OA\Property(format="string", title="path", default="envelopes.xlsx", description="envelopes.xlsx", property="path"),
     *          @OA\Property(format="string", title="import", default="optional|runs", description="import", property="import"),
     *     ),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     * @OA\Property(type="string", title="message", description="message", property="message", example="Import Category had been started, we will notify you later."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     * @param Request $request
     * @param string  $slug
     * @return JsonResponse
     */
    public function import(
        Request $request,
        string  $slug
    )
    {
        event(new CategoryImportExcel(tenant()->uuid, $request->all(), $slug));
        return response()->json(
            [
                "message" => _('Import Category had been started, we will notify you later.'),
                "status" => Response::HTTP_OK
            ]
        );
    }
}

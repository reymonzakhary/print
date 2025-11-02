<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories;

use App\Events\Tenant\Categories\CategoryExportExcel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryExportController extends Controller
{
    /**
     * @OA\Post (
     *     tags={"Category Export"},
     *     path="/api/v1/mgr/categories/{slug}/products/export",
     *     summary="Import Categories form excal",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(
     *          @OA\Property(format="string", title="type", default="xlsx", description="type", property="type"),
     *     ),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     * @OA\Property(type="string", title="message", description="message", property="message", example="Export Category had been started, we will notify you later."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     * @param Request $request
     * @param string  $slug
     * @return JsonResponse
     */
    public function __invoke(
        Request $request,
        string  $slug
    )
    {
        event(new CategoryExportExcel(tenant()->uuid, $slug, [
            "type" => $request->get('type'),
            "lang" => app()->getLocale()
        ], $request->user()));
        return response()->json(
            [
                "message" => _('Export Category had been started, we will notify you later.'),
                "status" => Response::HTTP_OK
            ]
        );
    }
}

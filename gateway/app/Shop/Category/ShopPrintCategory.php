<?php

namespace App\Shop\Category;

use App\Http\Controllers\Tenant\Mgr\Categories\CategoryController;
use App\Shop\Contracts\ShopCategoryInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopPrintCategory implements ShopCategoryInterface
{
    /**
     * @return AnonymousResourceCollection
     * @OA\Get (
     *     tags={"Shop"},
     *     path="/api/v1/mgr/shops/categories?type=print",
     *     summary="Get All products",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200",
     *  description="",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintCategoryResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example=""),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function categories()
    {
        //$ids =  auth()->user()->userTeams->map(fn ($team) => collect($team->print_categories)->pluck('_id'))->flatten(1)->pluck('$oid')->unique()->toArray();

        return app(CategoryController::class)->publishedCategory(request());
    }

    /**
     * @param int $category
     * @throws GuzzleException
     * @OA\Get (
     *     tags={"Category"},
     *     path="/api/v1/categories/{category}",
     *     summary="Get Category by ID",
     *     security={{ "Bearer":{} }},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(type="object", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function category(
        $category
    )
    {
        return app(CategoryController::class)->show($category);
    }
}

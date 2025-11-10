<?php

namespace App\Shop\Category;

use App\Http\Resources\Shops\ShopCategoryResource;
use App\Models\Tenant\Category;
use App\Shop\Contracts\ShopCategoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ShopCustomCategory implements ShopCategoryInterface
{
    /**
     * @return AnonymousResourceCollection
     * @OA\Get (
     *     tags={"Shop"},
     *     path="/api/v1/mgr/shops/categories",
     *     summary="Get All products",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200",
     *  description="",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/ShopCategoryResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example=""),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function categories()
    {
        $ids = auth()->user()->userTeams->map(fn ($team) => $team->category()->pluck('row_id'))->flatten(1)->toArray();

        return ShopCategoryResource::collection(
            Category::tree()
                ->whereIn('row_id', $ids)
                ->where([
                    ['iso', app()->getLocale()],
                    ['published', true]
                ])
                ->with('media')
                ->orderBy(request()->order_by ?? 'id', request()->order_dir ?? 'asc')
                ->get()->toTree()
        );
    }

    /**
     * @param mixed $category
     * @return JsonResponse|ShopCategoryResource
     * @OA\Get (
     *     tags={"Shop"},
     *     path="/api/v1/mgr/shops/category/{category}",
     *     summary="Get specific category by ID",
     *     security={{ "Bearer":{} }},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *         description="The ID of the category",
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/ShopCategoryResource"),
     *     ),
     *     @OA\Response(response=404, description="Category not found"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function category(
        $category
    )
    {
        // @todo Blocking the team users
        $category = Category::tree()
            ->where([
                ['iso', app()->getLocale()],
                ['published', true],
                ['slug', $category]
            ])
            ->with('media')
            ->first();

        if (!$category) {
            // Handle the case where the category is not found
            return response()->json([
                'message' => __('Category not found'),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        return ShopCategoryResource::make($category);
    }
}

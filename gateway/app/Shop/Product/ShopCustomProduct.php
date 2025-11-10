<?php

namespace App\Shop\Product;

use App\Http\Resources\Shops\ShopVariationsResource;
use App\Models\Tenant\Blueprint;
use App\Models\Tenant\Category;
use App\Shop\Contracts\ShopProductInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ShopCustomProduct implements ShopProductInterface
{

    /**
     * @var Category
     */
    private Category $category;


    /**
     * @throws ValidationException
     */
    public function setCategories(
        $category,
    ): static
    {
        if(!is_numeric($category)) {
            throw ValidationException::withMessages([
                'category' => [
                    __("Category must be a number, string given.")
                ]
            ]);
        }
        $this->category = Category::where(['row_id' => $category, 'iso' => app()->getLocale()])->first();
        return $this;
    }

    /**
     * @return AnonymousResourceCollection
     * @OA\Get (
     *     tags={"Shop"},
     *     path="/api/v1/mgr/shops/categories/{category_id}/products",
     *     summary="Get All products",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200",
     *  description="",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/ShopProductResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example=""),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function products(): AnonymousResourceCollection
    {
        return ShopVariationsResource::collection(
            $this->category->products()->where([
                ['iso', app()->getLocale()],
                ['published', true]
            ])->get()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK,
            'workflow' => Blueprint::where('ns', 'workflow_shop')->first()?->configuration
        ]);
    }
}

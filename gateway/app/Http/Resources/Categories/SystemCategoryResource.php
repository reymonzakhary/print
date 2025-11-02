<?php

namespace App\Http\Resources\Categories;

use App\Plugins\Moneys;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class SystemCategoryResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @param mixed $resource
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection($resource)
    {
        return tap(new SystemCategoryResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    /**
     * @OA\Property(format="int64", title="id", default=1, description="id", property="id"),
     * @OA\Property(format="int64", title="linked", description="linked", property="linked" ,example="null | 61b1b520a50498808633c525"),
     * @OA\Property(format="string", title="sort", default="", description="sort", property="sort"),
     * @OA\Property(format="string", title="tenant_id", description="tenant_id", property="tenant_id", example="a5c4fe5d-a4e4-42a6-9558-c566e1725691"),
     * @OA\Property(format="string", title="tenant_name", description="tenant_name", property="tenant_name", example="reseller.prindustry.test"),
     * @OA\Property(format="string", title="name", description="name", property="name", example="Posters"),
     * @OA\Property(format="string", title="slug", description="slug", property="slug", example="posters"),
     * @OA\Property(format="string", title="display_name", description="display_name", property="display_name", example="Posters"),
     * @OA\Property(type="array", property="countries", @OA\Items(ref="#/components/schemas/countriesArea")),
     * @OA\Property(format="string", title="description", description="description", property="description", example="Posters b"),
     * @OA\Property(format="string", title="sku", description="sku", property="sku", example="qweqe-322132-qweqw"),
     * @OA\Property(format="string", title="shareable", description="shareable", property="shareable", example="false"),
     * @OA\Property(format="string", title="published", description="published", property="published", example="true"),
     * @OA\Property(format="string", title="media", description="media", property="media", example="['image.jpg', 'image.jpg']"),
     * @OA\Property(property="price_build",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="collection", type="boolen", example="Ture"),
     *          @OA\Property(property="full_calculation", type="string", example="False"),
     *          @OA\Property(property="semi_calculation", type="string", example="Ture")
     *        )
     *     ),
     * @OA\Property(format="string", title="has_products", description="has_products", property="has_products", example="False"),
     * @OA\Property(format="string", title="has_manifest", description="has_manifest", property="has_manifest", example="False"),
     * @OA\Property(property="calculation_method",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="active", type="boolen", example="Ture"),
     *          @OA\Property(property="name", type="string", example="slide scale"),
     *          @OA\Property(property="slug", type="string", example="slide-scale")
     *        )
     *     ),
     * @OA\Property(property="dlv_days",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="days", type="boolen", example="1"),
     *          @OA\Property(property="label", type="string", example="Overnight"),
     *          @OA\Property(property="mode", type="string", example="fixed"),
     *          @OA\Property(property="value", type="string", example="1000")
     *        )
     *     ),
     * @OA\Property(property="printing_method",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="name", type="boolen", example="Digital"),
     *          @OA\Property(property="slug", type="string", example="digital"),
     *          @OA\Property(property="from", type="boolen", example="1"),
     *          @OA\Property(property="to", type="string", example="1000"),
     * 	@OA\Property(property="dlv_days",type="array" ,
     *       @OA\Items(
     * 	        @OA\Property(property="availability",type="array" ,
     *              @OA\Items(
     *                  @OA\Property(property="from", type="string", example="0"),
     *                  @OA\Property(property="to", type="string", example="1200")
     *              )
     *          ),
     *          @OA\Property(property="days", type="string", example="1")
     *        )
     *     ),
     * 	@OA\Property(property="qty_build",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="from", type="boolen", example="1"),
     *          @OA\Property(property="to", type="string", example="Overnight"),
     * 	        @OA\Property(property="price",type="array" ,
     *              @OA\Items(
     *                  @OA\Property(property="value", type="boolen", example="1"),
     *                  @OA\Property(property="mode", type="string", example="Overnight"),
     *                  @OA\Property(property="on", type="string", example="fixed")
     *              )
     *             ),
     *          @OA\Property(property="description", type="string", example="1000"),
     *          @OA\Property(property="incremental_by", type="string", example="1000")
     *        )
     *     ),
     *        )
     *     )
     *        )
     *     ),
     * @OA\Property(format="string", title="start_cost", description="start_cost", property="start_cost", example="start_cost"),
     * @OA\Property(format="date", title="created at", default="2021-09-08T12:19:37.000000Z", description="name", property="created_at"),
     */

    public function toArray($request): array
    {
        return [
            "id" => data_get($this->resource, '_id.$oid', data_get($this->resource, '_id', $this->resource['id'] ?? null)),
            "sort" => data_get($this->resource, 'sort'),
            "tenant_id" => data_get($this->resource, 'tenant_id'),
            "tenant_name" => data_get($this->resource, 'tenant_name'),
            "countries" => data_get($this->resource, 'countries'),
            "sku" => data_get($this->resource, 'sku'),
            "name" => data_get($this->resource, 'name'),
            "system_key" => data_get($this->resource, 'system_key'),
            "display_name" => data_get($this->resource, 'display_name'),
            "slug" => data_get($this->resource, 'slug'),
            "description" => data_get($this->resource, 'description'),
            "published" => data_get($this->resource, 'published'),
            "shareable" => data_get($this->resource, 'shareable'),
            "media" => data_get($this->resource, 'media'),
            "price_build" => data_get($this->resource, 'price_build'),
            "has_products" => data_get($this->resource, 'has_products'),
            "has_manifest" => data_get($this->resource, 'has_manifest'),
            "calculation_method" => data_get($this->resource, 'calculation_method'),
            "dlv_days" => data_get($this->resource, 'dlv_days'),
            "printing_method" => data_get($this->resource, 'printing_method'),
            "production_days" => data_get($this->resource, 'production_days'),
            "production_dlv" => data_get($this->resource, 'production_dlv'),
            "start_cost" => data_get($this->resource, 'start_cost'),
            "display_start_cost" => (new Moneys())
                ->setDecimal(5)
                ->setPrecision(5)
                ->setAmount(data_get($this->resource, 'start_cost'))
                ->format(),
            "linked" => data_get($this->resource, 'linked.$oid'),
            "ref_id" => data_get($this->resource, 'ref_id'),
            "ref_category_name" => data_get($this->resource, 'ref_category_name'),
            "additional" => $this->getAdditional(data_get($this->resource, 'additional', [])),
            "suppliers" => LinkedSupplierResource::collection(data_get($this->resource, 'suppliers', [])),
            "vat" => data_get($this->resource, 'vat'),
            "boops" => PrintBoopsResource::collection(data_get($this->resource, 'boops', [])),
            "created_at" => Carbon::createFromTimestamp(
                data_get($this->resource, 'created_at.$date', 0) / 1000, 'UTC'
            )->toDateTimeString(),
        ];
    }


    /**
     * @param array $additional
     * @return array
     */
    public function getAdditional(
        array $additional = []
    ): array
    {
        return collect($additional)->map(fn($v) => collect($v)->flatMap(fn($v, $k) => match ($k) {
            'machine' => [$k => optional($v)['$oid']],
            default => [$k => $v]
        }))->toArray();
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    public function hide(array $fields)
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param array $array
     * @return array
     */
    protected function filterFields(array $array)
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}

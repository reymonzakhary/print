<?php

namespace App\Http\Resources\Categories;

use App\Plugins\Moneys;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PrintCategoryResource
 * @package App\Http\Resources\Categories
 * @OA\Schema(
 *     schema="PrintCategoryResource",
 *     title="Print Category Resource"
 *
 * )
 */
class PrintCategoryResource extends JsonResource
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
        return tap(new PrintCategoryResourceCollection($resource), function ($collection) {
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

    public function toArray($request)
    {
        return [
            "id" => $this->id($this->resource),
            "sort" => optional($this->resource)['sort'],
            "tenant_id" => optional($this->resource)['tenant_id'],
            "tenant_name" => optional($this->resource)['tenant_name'],
            "countries" => optional($this->resource)['countries'],
            "sku" => optional($this->resource)['sku'],
            "name" => $this->resource['name'],
            "system_key" => $this->resource['system_key'],
            "display_name" => optional($this->resource)['display_name'],
            "slug" => $this->resource['slug'],
            "description" => optional($this->resource)['description'],
            "published" => optional($this->resource)['published'],
            "shareable" => optional($this->resource)['shareable'],
            "media" => optional($this->resource)['media'],
            "price_build" => optional($this->resource)['price_build'],
            "has_products" => optional($this->resource)['has_products'],
            "has_manifest" => optional($this->resource)['has_manifest'],
            "calculation_method" => optional($this->resource)['calculation_method'],
            "dlv_days" => optional($this->resource)['dlv_days'],
            "printing_method" => optional($this->resource)['printing_method'],
            "production_days" => optional($this->resource)['production_days'],
            "production_dlv" => optional($this->resource)['production_dlv'],
            "start_cost" => optional($this->resource)['start_cost'],
            "display_start_cost" =>  (new Moneys())->setDecimal(5)->setPrecision(5)->setAmount(optional($this->resource)['start_cost'])->format(),
            "linked" => optional(optional($this->resource)['linked'])['$oid'],
            "ref_id" => optional($this->resource)['ref_id'],
            "ref_category_name" => optional($this->resource)['ref_category_name'],
            "additional" => $this->getAdditional(optional($this->resource)['additional'] ?? []),
            "suppliers" => optional($this->resource)['suppliers'],
            "matches" => optional($this->resource)['matches'],
            "vat" => optional($this->resource)['vat'],
            "boops" => optional($this->resource)['boops'] ? PrintBoopsResource::collection(optional($this->resource)['boops']) : [],
            "created_at" => Carbon::createFromTimestamp(optional(optional($this->resource)['created_at'])['$date'] / 1000, 'UTC')->toDateTimeString()

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

    protected function id($obj)
    {
        return optional(optional($obj)['_id'])['$oid'] ?? optional($obj)['_id'];
    }
}

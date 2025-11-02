<?php

namespace App\Http\Resources\Categories;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * Class PrintBoopsResource
 * @package App\Http\Resources\Categories
 * @OA\Schema(schema="PrintBoopsResource",title="Print Boops Resource options")
 */
final class PrintBoopsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    /**
     * @OA\Property(format="int64", title="id", default="61cdc5a0bcc7630a69454e8b", description="id", property="id"),
     * @OA\Property(format="string", title="name", default="", description="name", example="Envelopes", property="name"),
     * @OA\Property(format="string", title="tenant_id", default="", description="tenant_id", example="2373d94c-0169-4e2b-8a33-35279ed9413c", property="tenant_id"),
     * @OA\Property(format="string", title="tenant_name", default="", description="tenant_name", example="drukwerkdeal", property="tenant_name"),
     * @OA\Property(format="string", title="display_name", default="", description="display_name", example="Envelopes", property="display_name"),
     * @OA\Property(format="string", title="price_build", default="", description="price_build", example="price_build", property="price_build"),
     * @OA\Property(format="boolean", title="published", default="true", description="published", example="true", property="published"),
     * @OA\Property(format="string", title="shareable", default="false", description="shareable", example="false", property="shareable"),
     * @OA\Property(type="array", property="boops", @OA\Items(ref="#/components/schemas/PrintBoopsResource_boxes")),
     * @OA\Property(format="string", title="media", default="", description="media", example="media", property="media"),
     * @OA\Property(format="string", title="linked", default="", description="linked", example="linked", property="linked"),
     * @OA\Property(format="string", title="generated", default="", description="generated", example="true", property="generated"),
     * @OA\Property(format="string", title="has_products", default="", description="has_products", example="null", property="has_products"),
     */
    public function toArray(
        Request $request
    ): array
    {
        return [
            "id" => data_get($this->resource, '_id.$oid') ?? data_get($this->resource, '_id', data_get($this->resource, 'id')),
            "name" => $this->resource['name'],
            "slug" => $this->resource['slug'],
            "sku" => optional($this->resource)['sku'],
            "sort" => optional($this->resource)['sort'],
            "tenant_id" => $this->resource['tenant_id'],
            "description" => optional($this->resource)['description'],
            "ref_id" => optional($this->resource)['ref_id'],
            "ref_boops_id" => optional(optional($this->resource)['ref_boops_id'])['$oid'],
            "ref_boops_name" => optional($this->resource)['ref_boops_name'],
            "tenant_name" => $this->resource['tenant_name'],
            "display_name" => optional($this->resource)['display_name'],
            "system_key" => optional($this->resource)['system_key'],
            "price_build" => optional($this->resource)['price_build'],
            "calculation_method" => optional($this->resource)['calculation_method'],
            "dlv_days" => optional($this->resource)['dlv_days'],
            "printing_method" => optional($this->resource)['printing_method'],
            "production_days" => optional($this->resource)['production_days'],
            "start_cost" => optional($this->resource)['start_cost'],
            "published" => optional($this->resource)['published'],
            "shareable" => optional($this->resource)['shareable'],
            "supplier_category" => $this->id($this->resource) ?? optional(optional($this->resource)['supplier_category'])['$oid'],
            //            FIXME :: dont Return checked form server
            //            "checked" => optional($this->resource)['checked'],
            "divided" => optional($this->resource)['divided'],
            "bleed" => optional($this->resource)['bleed'],
            "vat" => optional($this->resource)['vat'],
            "countries" => optional($this->resource)['countries'],
            "free_entry" => optional($this->resource)['free_entry'],
            "has_manifest" => optional($this->resource)['has_manifest'],
            "limits" => optional($this->resource)['limits'],
            "range_around" => optional($this->resource)['range_around'],
            "range_list" => optional($this->resource)['range_list'],
            "ranges" => optional($this->resource)['ranges'],
            "ref_category_name" => optional($this->resource)['ref_category_name'],
            "boops" => $this->boxes(optional($this->resource)['boops'] ?? []),
            "media" => optional($this->resource)['media'],
            "linked" => optional(optional($this->resource)['linked'])['$oid'],
            "generated" => optional($this->resource)['generated'],
            "has_products" => optional($this->resource)['has_products'],
            "additional" => $this->getAdditional(optional($this->resource)['additional'] ?? []),
            "created_at" => $this->extractAndFormatCreatedAtAttribute($this->resource)
        ];
    }

    /**
     * Extract and format a given `created_at` attribute
     *
     * @param mixed $resource
     *
     * @return string|null
     */
    private function extractAndFormatCreatedAtAttribute(
        mixed $resource
    ): ?string
    {
        $extractedDateString = $resource['created_at']['$date'] ?? $resource['created_at'] ?? false;

        return $extractedDateString ? Carbon::create($extractedDateString)->toDateTimeString() : null;
    }

    /**
     * @param array $additional
     * @return array
     */
    public function getAdditional(
        array $additional = []
    ): array
    {
        return collect($additional)->map(fn($v) => collect($v)->flatMap(function ($v, $k) {
            return match ($k) {
                'machine' => [$k => optional($v)['$oid'] ?? $v],
                default => [$k => $v]
            };
        }))->toArray();
    }

    /**
     * Class PrintBoopsResource_boxes
     * @OA\Schema(
     *     schema="PrintBoopsResource_boxes",
     *   description="PrintBoopsResource_boxes",
     *   title="Print Boops Resource Boxes",
     *    @OA\Property(format="int64", title="id", default=1, description="id", property="id", example="61cd9a77b4a5b863e8ef22ba"),
     *    @OA\Property(format="string", title="name", default=1, description="name", example="Printing Colors"),
     *    @OA\Property(format="string", title="display_name", default=1, description="display_name", property="display_name" , example="Printing Colors"),
     *    @OA\Property(format="string", title="slug", default=1, description="drukwerkdeal", property="slug" , example="printing-colors"),
     *    @OA\Property(format="string", title="description", default=1, description="description", property="description" , example=""),
     *    @OA\Property(format="string", title="media", default=1, description="[]", property="media" , example="[]"),
     *    @OA\Property(format="string", title="sqm", default=1, description="sqm", property="sqm" , example=""),
     *    @OA\Property(format="string", title="linked", default=1, description="linked", property="linked" , example=""),
     *    @OA\Property(format="string", title="input_type", default=1, description="input_type", property="input_type" , example=""),
     *    @OA\Property(format="boolean", title="published", default=1, description="published", property="published" , example="false"),
     *    @OA\Property(type="array", property="opts", @OA\Items(ref="#/components/schemas/PrintBoopsResource_options")),
     * )
     */

    public function boxes(
        array $boxes = []
    ): Collection
    {
        return collect($boxes)->map(fn($box) => [
            'id' => $this->getId($box),
            'ref_box' => optional(optional($box)['ref_box'])['$oid'],
            'name' => $box['name'],
            'calc_ref' => optional($box)['calc_ref'],
            'divider' => optional($box)['divider'],
            'display_name' => optional($box)['display_name'] ?? $box['name'],
            'system_key' => optional($box)['system_key'] ?? $box['name'],
            'slug' => $box['slug'],
            'source_slug' => optional($box)['source_slug'],
            'description' => optional($box)['description'],
            'appendage' => (bool) optional($box)['appendage'],
            'incremental' => (bool) optional($box)['incremental'],
            'media' => optional($box)['media'],
            'sqm' => optional($box)['sqm'],
            'linked' => $this->linked($box),
            'input_type' => optional($box)['input_type'],
            'sku' => optional($box)['sku'],
            'additional' => optional($box)['additional'],
            'published' => optional($box)['published'] ?? false,
            'ops' => $this->options($box['ops'])
        ]);
    }

    /**
     * Class PrintBoopsResource_options
     * @OA\Schema(
     *     schema="PrintBoopsResource_options",
     *   description="PrintBoopsResource_options",
     *   title="Print Boops Resource options",
     *    @OA\Property(format="int64", title="id", default=1, description="id", property="id", example="61cd9a79b4a5b863e8ef247a"),
     *    @OA\Property(format="string", title="name", default=1, description="name", property="name", example="1\/0 PMS"),
     *    @OA\Property(format="string", title="display_name", default=1, description="display_name", property="display_name", example="1\/0 PMS"),
     *    @OA\Property(format="string", title="slug", default=1, description="slug", property="slug", example="1-0-pms"),
     *    @OA\Property(format="string", title="description", default=1, description="description", property="description", example=""),
     *    @OA\Property(format="string", title="media", default=1, description="[]", property="media", example=""),
     *    @OA\Property(format="int64", title="unit", default=1, description="unit", property="unit", example=""),
     *    @OA\Property(format="int64", title="maximum", default=1, description="maximum", property="maximum", example=""),
     *    @OA\Property(format="int64", title="minimum", default=1, description="minimum", property="minimum", example=""),
     *    @OA\Property(format="string", title="incremental_by", default=1, description="incremental_by", property="incremental_by", example=""),
     *    @OA\Property(format="string", title="information", default=1, description="information", property="information", example=""),
     *    @OA\Property(format="boolean", title="published", default=1, description="published", property="published", example=""),
     *    @OA\Property(format="string", title="input_type", default=1, description="input_type", property="input_type", example=""),
     *    @OA\Property(format="string", title="linked", default=1, description="linked", property="linked", example=""),
     *    @OA\Property(format="string", title="excludes", default=1, description="excludes", property="excludes", example="[]"),
     * )
     */

    public function options(
        array $options
    ): Collection
    {
        return collect($options)->map(fn($option) => [
            'id' => $this->getId($option),
            'ref_option' => optional(optional($option)['ref_option'])['$oid'],
            'name' => $option['name'],
            'display_name' => optional($option)['display_name'] ?? $option['name'],
            'system_key' => optional($option)['system_key'] ?? $option['name'],
            'slug' => optional($option)['slug'],
            'source_slug' => optional($option)['source_slug'],
            'description' => optional($option)['description'],
            'media' => optional($option)['media'],
            'unit' => optional($option)['unit'],
            'incremental_by' => optional($option)['incremental_by'],
            'information' => optional($option)['information'],
            'published' => (bool) optional($option)['published'],
            'input_type' => optional($option)['input_type'],
            'linked' => $this->linked(optional($option)),
            'excludes' => optional($option)['excludes'] ?? [],
            "sku" => optional($option)['sku'],
            "dimension" => optional($option)['dimension'],
            "dynamic" => optional($option)['dynamic'],
            "dynamic_keys" => optional($option)['dynamic_keys'],
            "start_on" => optional($option)['start_on'],
            "end_on" => optional($option)['end_on'],
            "generate" => optional($option)['generate'],
            "dynamic_type" => optional($option)['dynamic_type'],
            "width" => optional($option)['width'],
            "maximum_width" => optional($option)['maximum_width'],
            "minimum_width" => optional($option)['minimum_width'],
            "height" => optional($option)['height'],
            "maximum_height" => optional($option)['maximum_height'],
            "minimum_height" => optional($option)['minimum_height'],
            "length" => optional($option)['length'],
            "maximum_length" => optional($option)['maximum_length'],
            "minimum_length" => optional($option)['minimum_length'],
            "additional" => optional($option)['additional']??[],
        ]);
    }

    public function linked($link)
    {

        if (optional($link)) {
            if (optional($link)['linked']) {
                if (optional($link['linked'])['_ref']) {
                    return $link['linked']['_ref']['$id']['$oid'];
                } else if (optional($link['linked'])['$id']) {
                    return data_get($link['linked'], '$id', data_get($link['linked'], '$id.$oid'));
                } elseif (optional($link['linked'])['$oid']) {
                    return $link['linked']['$oid'];
                } else {
                    return $link['linked'];
                }
            }
        }
        return "";
    }

    protected function id($obj)
    {
        return match (optional(optional($obj)['_id'])['$oid']) {
            null => optional($obj)['_id'] ?? optional($obj)['id'],
            default => optional(optional($obj)['_id'])['$oid']
        };
    }

    /**
     * Get the ID of an object
     *
     * @param mixed $obj The object
     * @return string|null The ID of the object
     */
    protected function getId(
        $obj
    )
    {
        if (is_string($obj['id'])) {
            return $obj['id'];
        }
        return is_array(optional($obj)['id']) ?
            optional(optional($obj)['id'])['$oid'] :
            optional(optional($obj)['id']);
    }
}

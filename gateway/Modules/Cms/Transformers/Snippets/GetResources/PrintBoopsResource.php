<?php

namespace Modules\Cms\Transformers\Snippets\GetResources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PrintBoopsResource extends JsonResource
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
   public function toArray($request)
   {
      return [
         "id" => $this->resource['_id']['$oid'],
         "name" => $this->resource['name'],
         "slug" => $this->resource['slug'],
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
         "supplier_category" => optional(optional($this->resource)['supplier_category'])['$oid'],
         //            FIXME :: dont Return checked form server
         //            "checked" => optional($this->resource)['checked'],
         "boops" => $this->boxes(optional($this->resource)['boops']),
         "media" => optional($this->resource)['media'],
         "linked" => optional(optional($this->resource)['linked'])['$oid'],
         "generated" => optional($this->resource)['generated'],
         "has_products" => optional($this->resource)['has_products'],
         "additional" => $this->getAdditional(optional($this->resource)['additional'] ?? []),
      ];
   }

   /**
    * @param array $additional
    * @return array
    */
   public function getAdditional(
      array $additional = []
   ): array {
      return collect($additional)->map(fn ($v) => collect($v)->flatMap(function ($v, $k) {
         return match ($k) {
            'machine' => [$k => optional($v)['$oid']],
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

    /**
     * @param array $boxes
     */
   public function boxes(array $boxes)
   {
      return collect($boxes)->map(fn ($box) => [
         'id' => $box['id']['$oid'],
         'ref_box' => optional(optional($box)['ref_box'])['$oid'],
         'name' => $box['name'],
         'display_name' => optional($box)['display_name'] ?? $box['name'],
         'system_key' => optional($box)['system_key'] ?? $box['name'],
         'slug' => $box['slug'],
         'description' => optional($box)['description'],
         'media' => $this->media(optional($box)['media']??[]),
         'sqm' => optional($box)['sqm'],
         'linked' => $this->linked($box),
         'input_type' => optional($box)['input_type'],
         'published' => optional($box)['published'] ?? false,
         'ops' => $this->options($box['ops'])
      ]);
   }

   /**
    * @param array $media
    */
   public function media($media)
   {
      return array_map(fn ($mdia) => Storage::disk('tenancy')->url(tenant()->uuid. '/' . $mdia), $media);
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

   public function options($options)
   {
      return collect($options)->map(fn ($option) => [
         'id' => $option['id']['$oid'],
         'ref_option' => optional(optional($option)['ref_option'])['$oid'],
         'name' => $option['name'],
         'display_name' => optional($option)['display_name'] ?? $option['name'],
         'system_key' => optional($option)['system_key'] ?? $option['name'],
         'slug' => optional($option)['slug'],
         'description' => optional($option)['description'],
         'media' => $this->media(optional($option)['media'] ?? []),
         'unit' => optional($option)['unit'],
         'incremental_by' => optional($option)['incremental_by'],
         'information' => optional($option)['information'],
         'published' => optional($option)['published'],
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
      ]);
   }

   public function linked($link)
   {

      if (optional($link)) {
         if (optional($link)['linked']) {
            if (optional($link['linked'])['_ref']) {
               return $link['linked']['_ref']['$id']['$oid'];
            } else if (optional($link['linked'])['$id']) {
               return $link['linked']['$id']['$oid'];
            } elseif (optional($link['linked'])['$oid']) {
               return $link['linked']['$oid'];
            }
         }
      }
      return "";
   }

   protected function id($obj)
   {
      return optional(optional($obj)['_id'])['$oid'] ?? optional($obj)['_id'];
   }
}

<?php

namespace App\Http\Resources\Machines\Options;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "linked" => $this->linked,
            "slug" => $this->slug,
            "display_name" => $this->display_name,
            "system_key" => $this->resource->system_key,
            "sort" => $this->sort,
            "name" => $this->name,
            "description" => $this->description,
            "media" => $this->media,
            "width" => $this->width,
            "height" => $this->height,
            "dimension" => $this->dimension,
            "dynamic" => $this->dynamic,
            "maximum_width" => $this->maximum_width,
            "minimum_width" => $this->minimum_width,
            "maximum_height" => $this->maximum_height,
            "minimum_height" => $this->minimum_height,
            "maximum_length" => $this->maximum_length,
            "minimum_length" => $this->minimum_length,
            "boxes" => $this->boxes,
            "additional" => $this->additional,
            "length" => $this->length,
            "unit" => $this->unit,
            "incremental_by" => optional($this)->incremental_by,
            "extended_fields" => $this->extended_fields,
            "published" => $this->published,
            "parent" => $this->parent,
            "input_type" => $this->input_type,
            "start_cost" => $this->start_cost,
            "calculation_method" => $this?->calculation_method ?? "",
            "runs" => $this->runs,
            "sheet_runs" => SheetRunResource::collection($this->sheet_runs),
            "rpm" => optional($this)->rpm,
            "shareable" => $this->shareable,
            "sku" => $this->sku,
            "has_children" => $this->has_children,
            "tenant_id" => $this->tenant_id,
            "tenant_name" => $this->tenant_name,
            "excludes" => $this->excludes ?? [],
            "created_at" => $this->created_at,
        ];
    }
}

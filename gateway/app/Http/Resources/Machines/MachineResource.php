<?php

namespace App\Http\Resources\Machines;

use App\Http\Resources\Machines\Options\OptionResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class MachineResource
 * @package App\Http\Resources\Machines
 * @OA\Schema(
 * )
 */
class MachineResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    /**
     * @OA\Property(format="int64", title="id", default=1, description="ID", property="id"),
     * @OA\Property(format="boolean", title="active", default="active", description="False", property="active", example="false" ),
     * @OA\Property(format="string", title="description", default="description", description="description", property="description"),
     * @OA\Property(format="int64", title="height", default="height", description="height", property="height", example="450" ),
     * @OA\Property(format="string", title="width", default="width", description="width", property="width", example="500" ),
     * @OA\Property(format="int64", title="machine_type", default="machine_type", description="machine_type", property="machine_type"),
     * @OA\Property(format="string", title="model", default="model", description="model", property="model"),
     * @OA\Property(format="int64", title="price", default="price", description="price", property="price" ,example="100"),
     * @OA\Property(format="boolean", title="primary", default="primary", description="primary", property="primary", example="false" ),
     * @OA\Property(format="string", title="tenant_id", default="tenant_id", description="tenant_id", property="tenant_id"),
     * @OA\Property(format="string", title="tenant_name", default="tenant_name", description="tenant_name", property="tenant_name"),
     * @OA\Property(format="string", title="title", default="title", description="title", property="title", example="indigo" ),
     * @OA\Property(format="string", title="calculation_build", default="calculation_build", description="calculation_build", property="calculation_build", example="{"bundle':'false','hour_rate':'false', 'start_cost':'true'}" ),
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            'name' => $this->name,
            'description' => optional($this)->description,
            'type' => $this->type,
            'unit' => $this->unit,
            'width' => $this->width,
            'height' => $this->height,
            'spm' => optional($this)->spm,
            'colors' => optional($this)->colors,
            'materials' => optional($this)->materials,
            'printable_frame_length_min' => optional($this)->printable_frame_length_min,
            'printable_frame_length_max' => optional($this)->printable_frame_length_max,

            'fed' => $this->fed,
            'ean' => $this->ean,
            'sqcm' => $this->sqcm,
            'pm' => $this->pm,
            'price' => $this->price,
            "display_price" => (new \App\Plugins\Moneys())->setAmount($this->price)->setPrecision(5)->setDecimal(5)->format(),
            "setup_time" => $this->setup_time,
            "cooling_time" => $this->cooling_time,
            "cooling_time_per" => $this->cooling_time_per,
            "mpm" => $this->mpm,
            "spoilage" => (int)$this->spoilage,
            "divide_start_cost" => $this->divide_start_cost,
            "wf" => $this->wf,
            "min_gsm" => $this->min_gsm,
            "max_gsm" => $this->max_gsm,
            "margin_top" => $this->margin_top,
            "margin_bottom" => $this->margin_bottom,
            "margin_right" => $this->margin_right,
            "margin_left" => $this->margin_left,
            "trim_area" => $this->trim_area,
            "trim_area_exclude_y" => $this->trim_area_exclude_y,
            "trim_area_exclude_x" => $this->trim_area_exclude_x,
            "attributes" => $this->attributes,
            "options" => OptionResource::collection(optional($this)->options ?? []),
        ];
    }
}

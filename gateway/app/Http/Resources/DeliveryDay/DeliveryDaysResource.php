<?php

namespace App\Http\Resources\DeliveryDay;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class DeliveryDaysResource
 * @package App\Http\Resources\DeliveryDay
 * @OA\Schema(schema="DeliveryDaysResource",title="Delivery Days Resource")
 */
class DeliveryDaysResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    /**
     * @OA\Property(format="string", title="label", default="", description="label", example="Day-3", property="label"),
     * @OA\Property(format="string", title="slug", default="", description="slug", example="day-3", property="slug"),
     * @OA\Property(format="string", title="iso", default="", description="iso", example="en", property="iso"),
     * @OA\Property(format="string", title="days", default="", description="days", example="3", property="days"),
     */
    public function toArray($request)
    {
        return [
            "label" => $this->label,
            "slug" => $this->slug,
            "iso" => $this->iso,
            "days" => $this->days,
            "mode" => $this->mode,
            "price" => $this->price
        ];
    }
}

<?php

namespace App\Http\Resources\Languages;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class LanguageResource
 * @package App\Http\Resources\Languages
 * @OA\Schema(
 *     schema="LanguageResource",
 *     title="Language Resource"
 *
 * )
 */
class LanguageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     * @OA\Property(format="string", title="name", default="shoes", description="name", property="name"),
     * @OA\Property(format="string", title="iso", default="en", description="iso", property="iso"),
     */
    public function toArray($request)
    {
        return [
            "name" => $this->name,
            "iso" => $this->iso
        ];
    }
}

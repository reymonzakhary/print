<?php

namespace App\Http\Resources\Machines\Options;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SheetRunResource extends JsonResource
{
   /**
    * Transform the resource into an array.
    *
    * @return array<string, mixed>
    */
   public function toArray(Request $request): array
   {
      return [
         "dlv_production" => $this->dlv_production,
         "machine" => $this->machine,
         "runs" => $this->getRuns($this->runs)
      ];
   }

   public function getRuns($request): array
   {
      return collect($request)->map(fn ($run) => [
         'from' => (int)$run->from,
         'to' => (int)$run->to,
         'price' => $run->price,
         'display_price' => (new \App\Plugins\Moneys())->setPrecision(5)->setDecimal(5)->setAmount($run->price)->format(),
      ])->toArray();
   }
}

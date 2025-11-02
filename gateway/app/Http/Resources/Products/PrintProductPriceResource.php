<?php

namespace App\Http\Resources\Products;

use App\Plugins\Moneys;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintProductPriceResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->filterFields([
            "id" => $this->resource['_id']['$oid'] ?? $this->resource['_id'],
            "supplier_id" => optional($this->resource)['supplier_id'],
            "supplier_product" => optional(optional($this->resource)['supplier_product'])['$oid'] ?? optional($this->resource)['supplier_product'],
            "supplier_name" => optional($this->resource)['supplier_name'],
            "host_id" => optional($this->resource)['host_id'],
            "tables" => $this->tables(optional($this->resource)['tables']),
            "created_at" => optional(optional($this->resource)['created_at'])['$date']
        ]);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param $data
     * @return $this
     */
    public function tables($data)
    {

        if ($data) {
            $data['display_p'] = ((new Moneys())->setAmount($data['p']))->format();
            $data['display_ppp'] = ((new Moneys())->setAmount($data['ppp']))->format();
            $data['resale_p'] = null;
            $data['resale_ppp'] = null;
            $data['display_resale_p'] = null;
            $data['display_resale_ppp'] = null;
        }
        return $data;
    }

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

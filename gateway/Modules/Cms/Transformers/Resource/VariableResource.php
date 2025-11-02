<?php

namespace Modules\Cms\Transformers\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'label' => $this->label,
            'name' => $this->name,
            'key' => $this->key,
            'data_type' => $this->data_type,
            'input_type' => $this->input_type,
            'default_value' => $this->default_value,
            'placeholder' => $this->placeholder,
            'class' => $this->class,
            'secure_variable' => $this->secure_variable,
            'multi_select' => $this->multi_select,
            'incremental' => $this->incremental,
            'min_count' => $this->min_count,
            'max_count' => $this->max_count,
            'min_size' => $this->min_size,
            'max_size' => $this->max_size,
            'properties' => $this->properties,
            'short_code' => "[[*block.{$this->key}? &name=`` &label=`{$this->label}`]]"
        ];
    }
}

<?php

namespace App\Http\Requests\Finder;

use App\Facades\Settings;
use App\Models\Website;
use Illuminate\Foundation\Http\FormRequest;

class FilterProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'product' => "nullable|array",
            'sortby' => "nullable|string",
            'sortdir' => "nullable|string",
            'qty' => "nullable|string",
            'dlv' => "nullable|integer",

            'suppliers' => 'nullable|array',

            'divided' => 'boolean',
            'quantity' => "integer|min:1",
            'range_override' => "boolean",
            'quantity_range_start' => "integer|min:0",
            'quantity_range_end' => "integer|min:0",
            'quantity_incremental_by' => "integer|min:0",
            'bleed' => "integer|min:0",
            'vat' => "numeric|min:0",
            'vat_override' => "boolean|nullable",
            'contract' => 'nullable|array',
        ];
    }

    protected function prepareForValidation(): void
    {
        /**
         * get all supplier uuid that able to share his product
         */
        $websites = Website::findEnabledPrimary()->get();
        $sites = [];
        foreach ($websites as $website) {
            foreach ($website->hostnames as $host) {
                $sites[] = [
                    'host_id' => $host->host_id,
                    'supplier_id' => $website->uuid
                ];
            }
        }
        $this->merge([
            'product' => $this->product ?? [],
            'vat' => $this->vat ?? Settings::vat()?->value,
            'range_override' => $this->range_override??false,
            'vat_override' => $this->override === null?false:$this->override,
            'suppliers' => $this->suppliers && count($this->suppliers)
                ? $this->suppliers :
                $sites,
        ]);
    }
}

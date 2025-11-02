<?php

namespace App\Http\Requests\Items;

use App\DTO\Tenant\Orders\ItemDTO;
use App\Enums\CategoryCalculationType;
use App\Facades\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class ItemStoreRequest extends FormRequest
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
            'reference' => 'nullable|string|max:100',
            'note' => 'nullable|string|min:3|max:255',

            'delivery_pickup' => [
                'nullable',
                'boolean'
            ],

            'delivery_separated' => [
                'nullable',
                'required_if:delivery_pickup,false',
                function ($attribute, $value, $fail) {
                    return request()->replace([$attribute => NULL]);
                }
            ],

            'addresses' => [
                'array',
                'required_if:delivery_separated,1',
            ],

            'address' => [
                'integer',
                'exists:addresses,id',
                'required_if:delivery_separated,0',
            ],

            'calculation_type' => ['required', 'string', new Enum(CategoryCalculationType::class)],

            'connection' => 'required|string',
            'tenant_id' => 'required|string',
            'tenant_name' => 'required|string',
            'external' => 'required|boolean',
            'external_id' => 'required|string',
            'external_name' => 'required|string',

            'type' => 'required|string',
            'divided' => 'required|boolean',
            'quantity' => 'required|numeric',
            'margin' => 'nullable|array',

            'items' => 'required|array',
            'items.*.key_link' => 'nullable|string',
            'items.*.key_display_name' => 'nullable|array',
            'items.*.key_id' => 'required|string',
            'items.*.key_appendage' => 'boolean|nullable', // Adjust if necessary
            'items.*.key_calc_ref' => 'nullable|string',
            'items.*.key_divider' => 'nullable',
            'items.*.key_start_cost' => 'nullable|numeric',
            'items.*.key_incremental' => 'nullable|boolean',
            'items.*.key' => 'required|string',
            'items.*.value_link' => 'nullable|string',
            'items.*.value' => 'required|string',
            'items.*.value_id' => 'required|string',
            'items.*.value_display_name' => 'nullable|array',
            'items.*.value_dimension' => 'nullable|string',
            'items.*.value_dynamic' => 'nullable|boolean',
            'items.*.value_dynamic_type' => 'nullable|string',
            'items.*.value_unit' => 'nullable|string', // Adjust to required if necessary
            'items.*.value_width' => 'nullable|integer',
            'items.*.value_maximum_width' => 'nullable|integer',
            'items.*.value_minimum_width' => 'nullable|integer',
            'items.*.value_height' => 'nullable|integer',
            'items.*.value_maximum_height' => 'nullable|integer',
            'items.*.value_minimum_height' => 'nullable|integer',

            'items.*.value_sides' => 'nullable|integer',
            'items.*.value_pages' => 'nullable|integer',

            'items.*.value_length' => 'nullable|integer',
            'items.*.value_minimum_length' => 'nullable|integer',
            'items.*.value_maximum_length' => 'nullable|integer',
            'items.*.value_start_cost' => 'nullable|integer',

            'product' => 'required|array',
            'product.*.key' => 'required|string',
            'product.*.value' => 'required|string',
            'product.*.key_id' => 'required|string',
            'product.*.value_id' => 'required|string',
            'product.*.divider' => 'nullable|string',
            'product.*.dynamic' => 'required|boolean',
            'product.*._' => 'array',
            'product.*.source_key' => 'nullable',
            'product.*.source_value' => 'nullable',

            'category' => 'required|array',
            'category.id' => ['required_unless:calculation_type,open_calculation,external_calculation', 'string'],
            'category.tenant_id' => 'required|string',
            'category.tenant_name' => 'required|string',
            'category.countries' => 'nullable|array',
            'category.name' => 'required|string',
            'category.slug' => 'required|string',
            'category.display_name' => 'required|array',
            'category.display_name.*.display_name' => 'required|string',
            'category.display_name.*.iso' => 'required|string|min:2',
            'category.price_build' => 'required_unless:calculation_type,open_calculation,external_calculation|array',
            'category.price_build.collection' => 'required_unless:calculation_type,open_calculation,external_calculation|boolean',
            'category.price_build.semi_calculation' => 'required_unless:calculation_type,open_calculation,external_calculation|boolean',
            'category.price_build.full_calculation' => 'required_unless:calculation_type,open_calculation,external_calculation|boolean',
            'category.calculation_method' => 'required_unless:calculation_type,open_calculation,external_calculation|array',
            'category.calculation_method.*.name' => 'required_unless:calculation_type,open_calculation,external_calculation|string',
            'category.calculation_method.*.slug' => 'required_unless:calculation_type,open_calculation,external_calculation|string',
            'category.calculation_method.*.active' => 'required_unless:calculation_type,open_calculation,external_calculation|boolean',
            'category.production_days' => 'required|array',
            'category.production_days.*.day' => 'required|string',
            'category.production_days.*.active' => 'required|boolean',
            'category.production_days.*.deliver_before' => 'required|string',
            'category.start_cost' => 'nullable|numeric',
            'category.linked' => 'nullable|string',
            'category.bleed' => 'required|integer',
            'category.ref_id' => 'nullable|string',
            'category.ref_category_name' => 'nullable|string',
            'category.vat' => 'required|numeric',
            'category.sku' => 'nullable|string',
            'price.id' => 'nullable|string',
            'price.pm' => 'nullable|string',
            'price.qty' => [
                'required',
                'integer',
                'min:1',
                'max:1000000000000000000'
            ],
            'price.dlv' => 'required|array',
            'price.dlv.days' => 'required|integer',
            'price.dlv.day' => 'required|string',
            'price.dlv.day_name' => 'required|string',
            'price.dlv.month' => 'required|string',
            'price.dlv.year' => 'required|integer',
            'price.dlv.actual_days' => 'required|integer',
            'price.dlv.type' => 'nullable|string',
            'price.p' => 'required|numeric',
            'price.ppp' => 'required|numeric',
            'price.selling_price_ex' => 'required|numeric',
            'price.selling_price_inc' => 'required|numeric',
            'price.profit' => 'required|numeric',
            'price.vat' => 'required|numeric',
            'price.vat_p' => 'required|numeric',
            'price.vat_ppp' => 'required|numeric',
            'price.gross_price' => 'required|numeric',
            'price.gross_ppp' => 'required|numeric',
            'price.discount' => 'nullable|array',
            'price.margins' => 'nullable|array',

            'calculation' => 'nullable|array',
            'calculation.name' => 'nullable|string',
            'calculation.items' => 'nullable|array',
            'calculation.items.*.key_link' => 'nullable|string',
            'calculation.items.*.key_id' => 'nullable|string',
            'calculation.items.*.key_appendage' => 'nullable|boolean',
            'calculation.items.*.key_calc_ref' => 'nullable|string',
            'calculation.items.*.key_start_cost' => 'nullable|numeric',
            'calculation.items.*.key_incremental' => 'nullable|boolean',
            'calculation.items.*.key' => 'nullable|string',
            'calculation.items.*.value_link' => 'nullable|string',
            'calculation.items.*.value' => 'nullable|string',
            'calculation.items.*.value_id' => 'nullable|string',
            'calculation.items.*.value_dimension' => 'nullable|string',
            'calculation.items.*.value_dynamic' => 'nullable|boolean',
            'calculation.items.*.value_unit' => 'nullable|string',
            'calculation.items.*.value_width' => 'nullable|numeric',
            'calculation.items.*.value_maximum_width' => 'nullable|numeric',
            'calculation.items.*.value_minimum_width' => 'nullable|numeric',
            'calculation.items.*.value_height' => 'nullable|numeric',
            'calculation.items.*.value_maximum_height' => 'nullable|numeric',
            'calculation.items.*.value_minimum_height' => 'nullable|numeric',
            'calculation.items.*.value_length' => 'nullable|numeric',
            'calculation.items.*.value_minimum_length' => 'nullable|numeric',
            'calculation.items.*.value_maximum_length' => 'nullable|numeric',
            'calculation.items.*.value_start_cost' => 'nullable|numeric',
            'calculation.dlv' => 'nullable|array',
            'calculation.dlv.*.days' => 'nullable|integer',
            'calculation.dlv.*.value' => 'nullable|numeric',
            'calculation.dlv.*.mode' => 'nullable|string',
            'calculation.machine' => 'nullable|array',
            'calculation.machine.id' => 'nullable|string',
            'calculation.machine.tenant_id' => 'nullable|string',
            'calculation.machine.tenant_name' => 'nullable|string',
            'calculation.machine.name' => 'nullable|string',
            'calculation.machine.type' => 'nullable|string',
            'calculation.machine.unit' => 'nullable|string',
            'calculation.machine.width' => 'nullable|numeric',
            'calculation.machine.height' => 'nullable|numeric',
            'calculation.machine.spm' => 'nullable|numeric',
            'calculation.machine.price' => 'nullable|numeric',
            'calculation.machine.sqcm' => 'nullable|numeric',
            'calculation.machine.ean' => 'nullable|integer',
            'calculation.machine.pm' => 'nullable|string',
            'calculation.machine.setup_time' => 'nullable|numeric',
            'calculation.machine.cooling_time' => 'nullable|numeric',
            'calculation.machine.cooling_time_per' => 'nullable|numeric',
            'calculation.machine.mpm' => 'nullable|numeric',
            'calculation.machine.divide_start_cost' => 'nullable|boolean',
            'calculation.machine.spoilage' => 'nullable|numeric',
            'calculation.machine.wf' => 'nullable|numeric',
            'calculation.machine.min_gsm' => 'nullable|numeric',
            'calculation.machine.max_gsm' => 'nullable|numeric',
            'calculation.machine.printable_frame_length_min' => 'nullable|numeric',
            'calculation.machine.printable_frame_length_max' => 'nullable|numeric',
            'calculation.machine.fed' => 'nullable|string',
            'calculation.machine.trim_area' => 'nullable|numeric',
            'calculation.machine.trim_area_exclude_y' => 'nullable|boolean',
            'calculation.machine.trim_area_exclude_x' => 'nullable|boolean',
            'calculation.machine.margin_right' => 'nullable|numeric',
            'calculation.machine.margin_left' => 'nullable|numeric',
            'calculation.machine.margin_top' => 'nullable|numeric',
            'calculation.machine.margin_bottom' => 'nullable|numeric',
            'calculation.colors' => 'nullable|array',
            'calculation.colors.run' => 'nullable|array',
            'calculation.colors.runs' => 'nullable|array',
            'calculation.colors.dlv' => 'nullable|array',
            'calculation.colors.price' => 'nullable|numeric',
            'calculation.colors.price_list' => 'nullable|array',
            'calculation.colors.rpm' => 'nullable|numeric',
            'calculation.row_price' => 'nullable|numeric',
            'calculation.duration' => 'nullable|array',
            'calculation.duration.fed' => 'nullable|string',
            'calculation.duration.machine_name' => 'nullable|string',
            'calculation.duration.machine_id' => 'nullable|string',
            'calculation.duration.duration' => 'nullable|integer',
            'calculation.duration.duration_mpm' => 'nullable|integer',
            'calculation.duration.duration_spm' => 'nullable|integer',
            'calculation.duration.end_at' => 'nullable|date',
            'calculation.duration.duration_type' => 'nullable|string',
            'calculation.price_list' => 'nullable|array',
            'calculation.details' => 'nullable|array',
            'calculation.details.format_width' => 'nullable|numeric',
            'calculation.details.format_height' => 'nullable|numeric',
            'calculation.details.width_with_bleed' => 'nullable|numeric',
            'calculation.details.height_with_bleed' => 'nullable|numeric',
            'calculation.details.height_with_trim_area_and_bleed' => 'nullable|numeric',
            'calculation.details.width_with_trim_area_and_bleed' => 'nullable|numeric',
            'calculation.details.material_used' => 'nullable|string',
            'calculation.details.wight_used' => 'nullable|numeric',
            'calculation.details.maximum_prints_per_sheet' => 'nullable|integer',
            'calculation.details.ps' => 'nullable|string',
            'calculation.details.position' => 'nullable|array',
            'calculation.details.printable_area_height' => 'nullable|numeric',
            'calculation.details.printable_area_width' => 'nullable|numeric',
            'calculation.details.machine_id' => 'nullable|string',
            'calculation.details.machine_name' => 'nullable|string',
            'calculation.details.machine_ean' => 'nullable|integer',
            'calculation.details.machine_sqm' => 'nullable|numeric',
            'calculation.details.machine_spoilage' => 'nullable|numeric',
            'calculation.details.machine_spm' => 'nullable|numeric',
            'calculation.details.machine_mpm' => 'nullable|numeric',
            'calculation.details.pm' => 'nullable|string',
            'calculation.details.fed' => 'nullable|string',
            'calculation.details.catalogue_supplier' => 'nullable|string',
            'calculation.details.catalogue_art_nr' => 'nullable|string',
            'calculation.details.catalogue_ean' => 'nullable|integer',
            'calculation.details.catalogue_width' => 'nullable|numeric',
            'calculation.details.catalogue_height' => 'nullable|numeric',
            'calculation.details.catalogue_length' => 'nullable|numeric',
            'calculation.details.catalogue_density' => 'nullable|numeric',
            'calculation.details.catalogue_price' => 'nullable|numeric',
            'calculation.details.catalogue_calc_type' => 'nullable|string',
            'calculation.details.lm_in_sqm' => 'nullable|numeric',
            'calculation.details.roll_in_sqm' => 'nullable|numeric',
            'calculation.details.sheet_in_sqm' => 'nullable|numeric',
            'calculation.details.amount_sqm_sheets_in_kg' => 'nullable|numeric',
            'calculation.details.amount_of_sheets_needed' => 'nullable|integer',
            'calculation.details.exact_used_amount_and_area_of_sheet' => 'nullable|integer',
            'calculation.details.amount_of_sheets_printed' => 'nullable|integer',
            'calculation.details.amount_of_lm' => 'nullable|numeric',
            'calculation.details.amount_of_lm_printed' => 'nullable|numeric',
            'calculation.details.amount_of_role_needed' => 'nullable|numeric',
            'calculation.details.start_cost' => 'nullable|numeric',
            'calculation.details.price_sqm' => 'nullable|numeric',
            'calculation.details.price_per_sheet' => 'nullable|numeric',
            'calculation.details.price_per_lm' => 'nullable|numeric',
            'calculation.details.total_sheet_price' => 'nullable|numeric',
            'calculation.details.color' => 'nullable|array',
            'calculation.details.color.run' => 'nullable|array',
            'calculation.details.color.runs' => 'nullable|array',
            'calculation.details.color.dlv' => 'nullable|array',
            'calculation.details.color.price' => 'nullable|numeric',
            'calculation.details.color.price_list' => 'nullable|array',
            'calculation.details.color.rpm' => 'nullable|numeric',
            'calculation.details.message' => 'nullable|string',
            'calculation.details.status' => 'nullable|boolean',
            'calculation.details.applied' => 'nullable|boolean',
            'calculation.details.material_cost' => 'nullable|numeric',
            'calculation.details.shipping_cost' => 'nullable|numeric',
            'calculation.price' => 'nullable|array',
            'calculation.price.*.profit' => 'nullable|numeric',
            'calculation.price.*.vat' => 'nullable|numeric',
            'calculation.price.*.vat_p' => 'nullable|numeric',
            'calculation.price.*.vat_ppp' => 'nullable|numeric',
            'calculation.price.*.gross_price' => 'nullable|numeric',
            'calculation.price.*.gross_price_ppp' => 'nullable|numeric',
            'calculation.price.*.gross_ppp' => 'nullable|string',
            'calculation.price.*.discount' => 'nullable|array',
            'calculation.price.*.margins' => 'nullable|array',

            'vat' => 'numeric|nullable',
            'shipping_cost' => 'numeric|nullable',
            'supplier_id' => 'nullable|string',
            'supplier_name' => 'nullable|string',
        ];
    }

    public function prepareForValidation(): void
    {
        // Convert string boolean values to actual booleans in product array
        if ($this->has('product') && is_array($this->product)) {
            $product = $this->product;
            foreach ($product as $key => $item) {
                if (isset($item['dynamic']) && is_string($item['dynamic'])) {
                    $product[$key]['dynamic'] = filter_var($item['dynamic'], FILTER_VALIDATE_BOOLEAN);
                }
            }
            $this->merge(['product' => $product]);
        }

        if($this->external) {
            $this->merge([
                'supplier_id' => $this->external_id,
                'supplier_name' => $this->external_name,
                'connection' => $this->external_id
            ]);
        }else{
            $this->merge([
                'supplier_id' => $this->tenant_id,
                'supplier_name' => $this->tenant_name,
                'connection' => $this->tenant_id
            ]);
        }
        $vat =  $this->input('price.vat');
        if ($this->connection !== tenant()->uuid) {
            $price = $this->input('price', []);
            $vat = 0;
            $price['vat'] = 0;
            $price['vat_p'] = 0;
            $price['vat_ppp'] = 0;
            $this->merge(['price' => $price]);
        }
        $req = match ($this->get('calculation_type')) {
          'open_calculation' => array_merge(
              ItemDTO::fromOpenProduct($this->all()),
              [
                  'shipping_cost' => $this->get('shipping_cost')??0,
                  'vat' => $vat,
              ]
          ),
            default => [
                'shipping_cost' => $this->get('shipping_cost')??0,
                'vat' => $vat,
            ]
        };

        $this->merge($req);
    }
}

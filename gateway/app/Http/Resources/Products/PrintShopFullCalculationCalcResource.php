<?php

namespace App\Http\Resources\Products;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintShopFullCalculationCalcResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "name" => optional($this->resource)['name'],
            "items" => optional($this->resource)['items'],
            "machine" => optional($this->resource)['machine'],
            "row_price" => optional($this->resource)['row_price'],
            "duration" => $this->duration(optional($this->resource)['duration']),
            "price_list" => optional($this->resource)['price_list'],
            "details" => $this->details(optional($this->resource)['details']),
            "price" => PrintPriceShopCalcResource::collection(optional($this->resource)['price']),
        ];
    }

    /**
     * Creates an array containing various details related to a printing job based on the input $details.
     *
     * @param array $details An array containing details related to a printing job.
     * @return array An array containing the following keys:
     *     - "format_width": The width of the format from the $details array if present, null otherwise.
     *     - "format_height": The height of the format from the $details array if present, null otherwise.
     *     - "width_with_bleed": The width with bleed from the $details array if present, null otherwise.
     *     - "height_with_bleed": The height with bleed from the $details array if present, null otherwise.
     *     - "height_with_trim_area_and_bleed": The height with trim area and bleed from the $details array if present, null otherwise.
     *     - "width_with_trim_area_and_bleed": The width with trim area and bleed from the $details array if present, null otherwise.
     *     - "material_used": The material used from the $details array if present, null otherwise.
     *     - "wight_used": The weight used from the $details array if present, null otherwise.
     *     - "maximum_prints_per_sheet": The maximum prints*/
    protected function details(
        array $details = []
    ): array
    {
        return [
            "binding_method" => optional($details)['binding_method'],
            "binding_direction" => optional($details)['binding_direction'],

            "endpaper" => optional($details)['endpaper'],
            "endpaper_quantity" => optional($details)['endpaper_quantity'],
            "endpaper_amount_of_sheets" => optional($details)['endpaper_amount_of_sheets'],
            "endpaper_amount_of_sheets_with_spoilage" => optional($details)['endpaper_amount_of_sheets_with_spoilage'],
            "endpaper_total_sheet_price" => optional($details)['endpaper_total_sheet_price'],

            "format_width" => optional($details)['format_width'],
            "format_height" => optional($details)['format_height'],
            "width_with_bleed" => optional($details)['width_with_bleed'],
            "height_with_bleed" => optional($details)['height_with_bleed'],
            "height_with_trim_area_and_bleed" => optional($details)['height_with_trim_area_and_bleed'],
            "width_with_trim_area_and_bleed" => optional($details)['width_with_trim_area_and_bleed'],
            "rotate_format" => optional($details)['rotate_format'],
            "rotate_catalogue" => optional($details)['rotate_catalogue'],

            "material_used" => optional($details)['material_used'],
            "wight_used" => optional($details)['wight_used'],
            "maximum_prints_per_sheet" => optional($details)['maximum_prints_per_sheet'],
            "ps" => optional($details)['ps'],
            "position" => optional($details)['position'],
            "printable_area_height" => optional($details)['printable_area_height'],
            "printable_area_width" => optional($details)['printable_area_width'],

            "pm" => optional($details)['pm'],
            "fed" => optional($details)['fed'],

            "catalogue_supplier" => optional($details)['catalogue_supplier'],
            "catalogue_art_nr" => optional($details)['catalogue_art_nr'],
            "catalogue_ean" => optional($details)['catalogue_ean'],
            "catalogue_width" => optional($details)['catalogue_width'],
            "catalogue_height" => optional($details)['catalogue_height'],
            "catalogue_length" => optional($details)['catalogue_length'],
            "catalogue_density" => optional($details)['catalogue_density'],
            "catalogue_thickness" => optional($details)['catalogue_thickness'],
            "catalogue_price" => optional($details)['catalogue_price'],
            "catalogue_calc_type" => optional($details)['catalogue_calc_type'],
            "lm_in_sqm" => optional($details)['lm_in_sqm'],
            "roll_in_sqm" => optional($details)['roll_in_sqm'],
            "sheet_in_sqm" => optional($details)['sheet_in_sqm'],
            "amount_sqm_sheets_in_kg" => optional($details)['amount_sqm_sheets_in_kg'],
            "amount_of_sheets_needed" => optional($details)['amount_of_sheets_needed'],
            "exact_used_amount_and_area_of_sheet" => optional($details)['exact_used_amount_and_area_of_sheet'],
            "amount_of_sheets_printed" => optional($details)['amount_of_sheets_printed'],
            "amount_of_lm" => optional($details)['amount_of_lm'],
            "amount_of_lm_printed" => optional($details)['amount_of_lm_printed'],
            "amount_of_role_needed" => optional($details)['amount_of_role_needed'],
            "start_cost" => optional($details)['start_cost'],
            "price_sqm" => optional($details)['price_sqm'],
            "price_per_sheet" => optional($details)['price_per_sheet'],
            "price_per_lm" => optional($details)['price_per_lm'],
            "total_sheet_price" => optional($details)['total_sheet_price'],
            "price_list" => optional($details)['price_list'],
            "color" => $this->colors(optional($details)['color'])
        ];
    }

    /**
     * Creates an array containing various color-related information based on the input $colors.
     *
     * @param array $colors The colors data to process.
     * @return array An array containing the following keys:
     *     - "run": The run data from the $colors array if present, null otherwise.
     *     - "dlv": The dlv data from the $colors array if present, null otherwise.
     *     - "price": The price from the $colors array if present, null otherwise.
     *     - "price_list": The price list from the $colors array if present, null otherwise.
     *     - "rpm": The RPM from the $colors array if present, null otherwise.
     */
    protected function colors(
        array $colors = []
    ): array
    {
        return [
            "run" => optional($colors)['run'],
            "dlv" => optional($colors)['dlv'],
            "price" => optional($colors)['price'],
            "price_list" => optional($colors)['price_list'],
            "rpm" => optional($colors)['rpm'],
        ];
    }

    /**
     * Creates an array containing various duration-related information based on the input $duration.
     *
     * @param mixed $duration The duration data to process.
     * @return array An array containing the following keys:
     *     - "fed": The fed data from the $duration array if present, null otherwise.
     *     - "machine_name": The machine name from the $duration array if present, null otherwise.
     *     - "machine_id": The machine ID from the $duration array if present, null otherwise.
     *     - "duration": The duration from the $duration array if present, null otherwise.
     *     - "duration_mpm": The duration per minute from the $duration array if present, null otherwise.
     *     - "duration_spm": The duration per second from the $duration array if present, null otherwise.
     *     - "end_at": The end time calculated based on the start time from the request and the duration.
     *     - "duration_type": The duration type from the $duration array if present, null otherwise.
     */
    protected function duration(
        $duration
    )
    {
        return [
            "fed" => optional($duration)['fed'],
            "machine_name" => optional($duration)['machine_name'],
            "machine_id" => optional($duration)['machine_id'],
            "duration" => optional($duration)['duration'],
            "duration_mpm" => optional($duration)['duration_mpm'],
            "duration_spm" => optional($duration)['duration_spm'],
            "end_at" => Carbon::parse(request()->start_at)->addMinutes((int) round(optional($duration)['duration']??0)),
            "duration_type" => optional($duration)['duration_type'],
        ];
    }
}

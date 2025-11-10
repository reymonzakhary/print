<?php

namespace App\Http\Resources\Categories;

use App\Foundation\ContractManager\Facades\ContractManager;
use App\Models\Domain;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class FinderMarketplaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => data_get($this->resource, '_id.$oid', data_get($this->resource, '_id')),
            "slug" => $this->resource['slug'],
            "name" => $this->resource['name'],
            "countries" => optional($this->resource)['countries'],
            "sku" => optional($this->resource)['sku'],
            "display_name" => optional($this->resource)['display_name'],
            "published" => optional($this->resource)['published'],
            "shareable" => optional($this->resource)['shareable'],
            "generated" => optional($this->resource)['generated'],
            "checked" => optional($this->resource)['checked'],
            "sort" => optional($this->resource)['sort'],
            "iso" => optional($this->resource)['iso'],
            "description" => optional($this->resource)['description'],
            "boops" => $this->boxes(optional($this->resource)['boops']),
            "media" => optional($this->resource)['media'],
            "properties_manifest" => $this->properties(optional($this->resource)['properties_manifest']??[])
        ];

    }


    /**
     * Get properties information for the given array of properties.
     *
     * @param array $props The array of properties to retrieve information for.
     * @return array An array containing properties information, including tenant_name, logo URL,
     * tenant_id, and properties for each item in the input array.
     */
    public function properties(
        array $props = []
    ): array
    {
        return collect($props)->map(function ($item) {
            $contract= [];
            $custom_fields = null;
            $hostname_id = null;
            $website_id = null;
            $company_name = null;
            $page_title = null;
            $page_description = null;
            $page_media = null;
            if(tenant()->uuid !== $item['tenant_id']) {
                $website = Website::where('uuid', $item['tenant_id'])->with('hostname')->first();

                if(!$website->hostname){
                    return false;
                }
                $website_id = $website->id;
                $hostname_id = $website->hostname->id;
                $company_name = $website->hostname->custom_fields->pick('company_name');
                $page_title = $website->hostname->custom_fields->pick('page_title');
                $page_description = $website->hostname->custom_fields->pick('page_description');
                $page_media = $website->hostname->custom_fields->pick('page_media');
                $contract = ContractManager::getContractsBetween(
                    Domain::class,
                    \domain()->id,
                    Domain::class,
                    $website->hostname->id
                )->first();
                $custom_fields = optional($contract)->custom_fields;
            }


           return [
               'company_name' => $company_name,
               'page_title' => $page_title,
               'page_description' => $page_description,
               'page_media' => $page_media,
               'tenant_name' => $item['tenant_name'],
               'website_id' => $website_id,
               'hostname_id' => $hostname_id,
               'is_me' => tenant()->uuid === $item['tenant_id'],
               'contract' => $contract,
               'discount' => optional($custom_fields)->discount,
               'available_categories' => optional($custom_fields)->categories,
               'can_request_quotation' => optional($custom_fields)->canRequestQuotation,
               'logo' =>  $this->getLogo($item['tenant_id']),
               'tenant_id' => $item['tenant_id'],
               'properties' => $item['properties'],
               'boops' => $this->boxes($item['boops'] ?? []),
           ];
        })->toArray();
    }

    /**
     * Get the logo based on the UUID provided.
     *
     * @param string $uuid The UUID to search for.
     * @return string|null The URL of the logo if found, else returns null.
     */
    protected function getLogo(
        string $uuid
    ): ?string
    {
        if($uuid !== tenant()->uuid) {
            $hostname = Website::where('uuid', $uuid)->with('hostname')->first();
            return $hostname->hostname->logo?Storage::disk('digitalocean')->url($hostname->hostname->logo):null;
        }
        return domain()->logo?Storage::disk('digitalocean')->url(domain()->logo):null;
    }

    /**
     * Process an array of boxes and transform the data accordingly.
     * Returns a collection of transformed box data.
     *
     * @param array $boxes Array of boxes to process
     * @return Collection
     */
    public function boxes(array $boxes)
    {
        return collect($boxes)->map(fn($box) => [
            'id' => data_get($box, 'id', data_get($box, '_id.$oid')),
            'name' => optional($box)['name'],
            'system_key' => optional($box)['system_key'],
            'display_name' => optional($box)['display_name'] ?? $box['name'],
            'slug' => $box['slug'],
            'description' => $box['description'],
            'media' => $box['media'],
            'sqm' => $box['sqm'],
            'appendage' => $box['appendage'],
            'calculation_type' => $box['calculation_type'],
            'input_type' => $box['input_type'],
            'published' => $box['published'],
            'divider' => optional($box)['divider'],
            'linked' => optional($box)['linked'],
            'ops' => $this->options($box['ops'])
        ]);

    }

    public function options($options)
    {
        return collect($options)->map(function ($option) {
            if (is_array($option)) {
                return [
                    'id' => data_get($option, 'id', data_get($option, '_id.$oid')),
                    'name' => $option['name'],
                    'display_name' => optional($option)['display_name'] ?? $option['name'],
                    'slug' => optional($option)['slug'],
                    'system_key' => optional($option)['system_key'],
                    'description' => optional($option)['description'],
                    'information' => optional($option)['information'],
                    'media' => optional($option)['media'],
                    'dimension' => optional($option)['dimension'],
                    'dynamic' => optional($option)['dynamic'],
                    'sheet_runs' => optional($option)['sheet_runs'],
                    'width' => optional($option)['width'],
                    'maximum_width' => optional($option)['maximum_width'],
                    'minimum_width' => optional($option)['minimum_width'],

                    'height' => optional($option)['height'],
                    'maximum_height' => optional($option)['maximum_height'],
                    'minimum_height' => optional($option)['minimum_height'],


                    'length' => optional($option)['length'],
                    'maximum_length' => optional($option)['maximum_length'],
                    'minimum_length' => optional($option)['minimum_length'],
                    'unit' => optional($option)['unit'],

                    'excludes' => optional($option)['excludes'],
                    'dynamic_keys' => optional($option)['dynamic_keys'],
                    'start_on' => optional($option)['start_on'],
                    'end_on' => optional($option)['end_on'],
                    'dynamic_type' => optional($option)['dynamic_type'],
                    'generate' => optional($option)['generate'],
                    'dynamic_object' => optional($option)['dynamic_object'],
                    'linked' => optional($option)['linked'],

                ];
            }

        });

    }

    protected function id($obj)
    {
        if (is_array($obj)) {
            return optional($obj['_id'])['$oid'] ?? $obj['_id'];
        }
    }
}

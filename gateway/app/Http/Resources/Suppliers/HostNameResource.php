<?php

namespace App\Http\Resources\Suppliers;

use App\Services\Suppliers\SupplierCategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HostNameResource extends JsonResource
{

    protected array $defaultHide = ['created_at'];
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @param $resource
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection($resource)
    {
        return tap(new HostNameResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'website_id' => $this?->website_id??$this->website?->id,
            'uuid' => $this->uuid,
            'host_id' => $this->host_id,
            'name' => $this->fqdn,
            'logo' => $this?->logo ?Storage::disk('digitalocean')?->url($this->logo):null,
            'config' => $this->configure,
            'address' => $this->address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'supplier_info' => $this->custom_fields->except(['password', 'ready', 'email_send']),
            'shared_categories' => (int) app(SupplierCategoryService::class)->obtainCategoriesCount($this?->uuid??$this->website?->uuid),
            'has_handshake' => (bool) $this->contract?->id,
            'contract' => HandshakeResource::make($this->contract),
            'external' => $this->external,
            'supplier' => $this->supplier,
            'configure' => collect($this->configure)->get('auth')??[],
        ]);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    public function hide(array $fields)
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param $array
     * @return array
     */
    protected function filterFields(array $array)
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}

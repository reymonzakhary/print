<?php


namespace App\Http\Resources\Settings;


use App\Http\Responses\ResponderFormType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class SettingResource
 * @package App\Http\Resources\Settings
 * @OA\Schema(
 * )
 */
class SettingResource extends JsonResource
{
    protected array $defaultHide = ['created_at', 'updated_at'];

    /**
     * @var array
     */
    protected array $withoutFields = [];


    /**
     * @param mixed $resource
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection($resource)
    {
        return tap(new SettingResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    /**
     * @OA\Property(format="int64", title="ID", default=1, description="ID", property="id"),
     * @OA\Property(format="string", title="sort", default="0", description="sort", property="sort"),
     * @OA\Property(format="string", title="name", default="Language", description="name", property="name"),
     * @OA\Property(format="string", title="key", default="language", description="key", property="key"),
     * @OA\Property(format="string", title="secure_variable", default="false", description="secure_variable", property="secure_variable"),
     * @OA\Property(format="string", title="data_type", default="string", description="data_type", property="data_type"),
     * @OA\Property(format="string", title="data_variable", default="null", description="data_variable", property="data_variable"),
     * @OA\Property(format="string", title="multi_select", default="false", description="multi_select", property="multi_select"),
     * @OA\Property(format="string", title="incremental", default="false", description="incremental", property="incremental"),
     * @OA\Property(format="string", title="namespace", default="general", description="namespace", property="namespace"),
     * @OA\Property(format="string", title="area", default="core", description="area", property="area"),
     * @OA\Property(format="string", title="lexicon", default="en", description="lexicon", property="lexicon"),
     * @OA\Property(format="string", title="value", default="en", description="value", property="value"),
     * @OA\Property(format="string", title="ctx", default="1", description="ctx", property="ctx"),
     * @OA\Property(format="date", title="created_at", default="2021-09-08T12:19:37.000000Z", description="created_at", property="created_at"),
     * @OA\Property(format="date", title="updated_at", default="2021-09-08T12:19:37.000000Z", description="updated_at", property="updated_at"),
     */
    public function toArray($request)
    {
        $responder = new ResponderFormType();
        return $this->filterFields([
            'id' => $this->id,
            'sort' => $this->sort,
            'name' => $this->name,
            'key' => $this->key,
            'user_id' => $this->user_id,
            'description' => $this->description,
            'secure_variable' => $this->secure_variable,
            'data_model' => $this->data_model,
            'data_type' => $this->data_type,
            'data_variable' => $responder($this, 'data_variable'),
            'multi_select' => $this->multi_select,
            'incremental' => $this->incremental,
            'namespace' => $this->namespace,
            'area' => $this->area,
            'lexicon' => $this->lexicon,
            'value' => $responder($this, 'value'),
            'ctx' => $this->ctx,
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

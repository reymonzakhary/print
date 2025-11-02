<?php

namespace App\Http\Resources\UserSettings;

use App\Http\Responses\ResponderFormType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserSettingResource
 * @package App\Http\Resources\UserSettings
 * @OA\Schema(
 * )
 */
class UserSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
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
        $responder = new ResponderFormType();
        return [
            'id' => $this->id,
            'sort' => $this->sort,
            'name' => $this->name,
            'key' => $this->key,
            'secure_variable' => $this->secure_variable,
            'data_type' => $this->data_type,
            'data_variable' => $responder($this, 'data_variable'),
            'multi_select' => $this->multi_select,
            'incremental' => $this->incremental,
            'namespace' => $this->namespace,
            'area' => $this->area,
            'lexicon' => $this->lexicon,
            'value' => $responder($this, 'value'),
            'ctx' => $this->ctx,
        ];
    }
}

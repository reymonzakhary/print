<?php

namespace Modules\Campaign\Entities;

use App\Models\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use JsonException;

class CampaignExport extends Model
{
    use InteractsWithMedia;

    protected $fillable = [
        'finished', 'path', 'type', 'created_at'
    ];

    /**
     * @param $value
     * @return mixed
     * @throws JsonException
     */
    public function getPathAttribute(
        $value
    )
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     * @return string
     * @throws JsonException
     */
    public function setPathAttribute(
        $value
    )
    {
        return $this->attributes['path'] = json_encode($value, JSON_THROW_ON_ERROR);
    }
}

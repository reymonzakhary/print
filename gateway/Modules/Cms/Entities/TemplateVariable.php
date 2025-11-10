<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JsonException;
use Spatie\EloquentSortable\SortableTrait;

class TemplateVariable extends Model
{
    use SortableTrait;

    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'label',
        'template_id',
        'variable_id',
        'key',
        'folder_id',
        'data_type',
        'input_type',
        'defualt_value',
        'data_variable',
        'placeholder',
        'class',
        'secure_variable',
        'multi_select',
        'incremental',
        'min_count',
        'max_count',
        'min_size',
        'max_size',
        'properties'
    ];

    /**
     * @return BelongsTo
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * @return BelongsTo
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * @return BelongsTo
     */
    public function variable()
    {
        return $this->belongsTo(Variable::class);
    }

    /**
     * @param $value
     * @return mixed
     * @throws JsonException
     */
    public function getPropertiesAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     *
     */
    protected static function booted()
    {
        static::saving(function ($template_variable) {
            if (request()->properties) {
                $template_variable->properties = json_encode(request()->properties);
            }
        });
    }

}

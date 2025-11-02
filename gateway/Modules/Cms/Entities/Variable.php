<?php

namespace Modules\Cms\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;

class Variable extends Model
{
    use SortableTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'label',
        'key',
        'folder_id',
        'data_type',
        'input_type',
        'default_value',
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

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function getPropertiesAttribute($value)
    {
        return json_decode($value);
    }

    protected static function booted()
    {
        static::saving(function ($template_variable) {
            if (request()->properties) {
                $template_variable->properties = json_encode(request()->properties);
            }
        });
    }

}

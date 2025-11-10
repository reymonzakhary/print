<?php

namespace App\Models\Tenant;

use App\Models\Traits\CanBeScoped;
use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Blueprint extends Model implements Sortable
{
    use Slugable, SortableTrait, CanBeScoped;

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'slug', 'ns', 'blueprint', 'configuration', 'sort'];

    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    /***************************************
     * Setters
     ****************************************/

    /**
     * @param $value
     * @throws JsonException
     */
    public function setBlueprintAttribute(
        $value
    ): void
    {
        $this->attributes['blueprint'] = json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * @param $value
     * @throws JsonException
     */
    public function setConfigurationAttribute(
        $value
    ): void
    {
        $this->attributes['configuration'] = json_encode($value, JSON_THROW_ON_ERROR);
    }


    /***************************************
     * Getters
     ****************************************/
    /**
     * @param $value
     * @return mixed
     * @throws JsonException
     */
    public function getBlueprintAttribute(
        $value
    ): mixed
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getConfigurationAttribute(
        $value
    ): mixed
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     * @return string
     */
    public function getNsAttribute(
        $value
    ): string
    {
        $l = explode('.', $value);
        return array_shift($l);
    }

    /**
     *
     */
    public function products()
    {

        return $this->morphedByMany(
            Product::class,
            'blueprintable',
            'blueprintables',

            'blueprint_id',
            'blueprintable_id',

            'id',
            'row_id',
        )
            ->where('iso', app()->getLocale())
            ->using(Blueprintable::class)
            ->withPivot('step', 'queueable');
    }
}

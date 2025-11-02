<?php


namespace App\Contracts;


use Illuminate\Database\Eloquent\Model;

abstract class InitModelAbstract
{
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var array
     */
    protected array $hide = [];

    /**
     * ModelRepository constructor.
     * @param Model $model
     */
    public function __construct(
        Model $model
    )
    {
        $this->model = $model;
    }

    /**
     * @param array $hide
     * @return $this
     */
    final public function hide(
        array $hide
    ): self
    {
        $this->hide = $hide;
        return $this;
    }

    /**
     * @return array|Model|null
     */
    public function fillable()
    {
        return array_values(collect($this->model->getFillable())->reject(function ($v) {
            return in_array($v, $this->hide, true);
        })->toArray());
    }


}

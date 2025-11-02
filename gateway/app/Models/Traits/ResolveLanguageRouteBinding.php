<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;

trait ResolveLanguageRouteBinding
{

    protected string $column = 'row_id';

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return $this->column;
    }

    /**
     * @param mixed $value
     * @param null  $field
     * @return Model|void|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where([[$this->column, (int)$value], ['iso', app()->getLocale()]])->first() ??
            abort(404, __('Not Found -- There is no order found'));
    }
}

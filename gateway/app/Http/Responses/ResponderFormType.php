<?php


namespace App\Http\Responses;


use Illuminate\Support\Str;

class ResponderFormType
{

    /**
     * @param        $obj
     * @param string $action
     * @return mixed
     */
    public function __invoke($obj, string $action = 'value')
    {
        return $this->{$obj->data_type}($obj, $action);
    }

    /**
     * @param $method
     * @param $args
     * @return string
     */
    public function __call($method, $args)
    {
        $class = '\App\Actions\SettingAction\\' . Str::ucfirst(Str::camel($args[1]));
        if (method_exists($class, $method)) {
            return $class::$method($args[0]);
        }
    }
}

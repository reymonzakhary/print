<?php

namespace Modules\Cms\Foundation\Helpers\Data;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Cms\Entities\Chunk;
use Modules\Cms\Foundation\Traits\LoginHelperTrait;

class Errors
{
    private $data = [];

    private $messages = [];
    
    public function __construct() {
        $this->messages = session()->get('errors')?->toArray()??[];
    }

    /**
     * @param string $key
     * @param string $value
     * @return void
     * fill class with data
     */
    public function set(string $key,string  $value): void
    {
        $this->data[$key] = $value;
    }

    /** 
     * @param string $prop
     * @return string
     * get data from the class
    */
    public function __get($prop)
    {
        if (array_key_exists($prop, $this->messages)) {
            $key = $this->messages[$prop];
            return is_array($key) ? collect($key)->first() : $key;
        } else if (array_key_exists($prop, $this->data)) {
            $key = $this->data[$prop];
            return is_array($key) ? collect($key)->first() : $key;
        }
        return null;
    }

    /** 
     * @param string $property
     * @return string
     * renders the template
    */
    public function getHtml($property)
    {
        if (! $this->{$property}) {
            return '';
        }
        return preg_replace_callback('/\[\[\+error\]\]/m', function($match) use ($property) {
            return $this->{$property};
        }, $this->template??'');
    }

    /** 
     * @param array $data
     * @return self
     * fill class with pulck data 
    */
    public function fill(array $data)
    {
        $this->data = $data;
        return $this;
    }

}

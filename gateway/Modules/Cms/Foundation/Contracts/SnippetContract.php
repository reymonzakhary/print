<?php

namespace Modules\Cms\Foundation\Contracts;

use Illuminate\Support\Facades\Cache;
use Modules\Cms\Entities\Chunk;
use stdClass;

abstract class SnippetContract
{
    protected $data = [];
    
    protected object $inputs;

    public function __construct()
    {
        $this->inputs = new stdClass();
    }

    protected $request;

    /** 
     * @param $request
     * @return self
     * build the object with request instance to handle the login request
    */
    abstract public static function build($request);
    /** 
     * @param void
     * @return string
    */
    abstract public function getChunk();

    /** 
     * @param $key, $value
     * @return void
     * 
     * set class data
    */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /** 
     * @param $prop
     * @return null|string
     * get data from the class
    */
    public function __get($prop)
    {
        if (array_key_exists($prop, $this->data)) {
            return $this->data[$prop];
        }
        return null;
    }

    /** 
     * @param array $data
     * @return self
     * fill class with bulk data
    */
    public function fill(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * takes value and returns a boolean number corresponding to the string value
     *
     * @param mixed $value
     * @return boolean
     */
    protected function accepted($value):bool
    {
        if (!$value) { return false; }

        return match (strtolower($value)) {
            'true' => true,
            'yes' => true,
            'on' => true,
            '1' => true,
            'false' => false,
            'no' => false,
            'off' => false,
            '0' => false,

            default => true,
        };
    }

    protected function params($string): array
    {
        preg_match_all('/&([^&=]+)=`([^&=]+)`/sm', $string, $params);
        return array_combine($params[1], $params[2]);
    }

    public function getChunkFromCacheOrDB($chunkName)
    {
        if ($chunkName === null) {
            return null;
        }
        return $this->getChunkFromCache($chunkName)
                ?? Chunk::where('name', $chunkName)->first();
    }
    public function getChunkFromCache($chunkName)
    {
        return Cache::get(tenant()->uuid.'.chunk.'.$chunkName);
    }

}

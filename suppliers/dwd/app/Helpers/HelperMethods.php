<?php

namespace App\Helpers;

class HelperMethods
{
    /**
     * exclude some keys from array
     * 
     * @param array $array
     * @param array $keys
     * @return array
     */
    public function array_except($array, $keys) 
    {
        return array_diff_key($array, array_flip((array) $keys));
    }

    /**
     * return created instances in our DB
     * 
     * @param Illuminate\Database\Eloquent\Model $model
     * @return array
     */
    public function getCreated($model) 
    {
        return $model::get();
    }

    /**
     * return created json fields in our DB
     * 
     * @param Illuminate\Database\Eloquent\Model $model
     * @return array
     */
    public function findCreated($model, $key, $value) 
    {
        return $model::where($key, $value)->get();
    }


    /**
     * return instance with realtions from DB
     * 
     * @param Illuminate\Database\Eloquent\Model $model
     * @param array $relations
     * @return array
     */
    public function getWithRelation($model, $relations) 
    {
        return  $model::with($relations)->get();
    }

    /**
     * return instance with realtions from DB
     * 
     * @param Illuminate\Database\Eloquent\Model $model
     * @param Int $id
     * @param array $relations
     * @return array
     */
    public function findWithRelation($model, $id , $relations) 
    {
        return  $model::where('id', $id)->with($relations)->firstOrFail();
    }

    /**
     * return chosen param from model, with deep search
     * 
     * @param Illuminate\Database\Eloquent\Model $model
     * @param Int $id
     * @param array $relations
     * @return array
     */
    public function pluckFromArray($model, $where, $collection, $param) 
    {
        return  $model::whereIn( $where, [ $collection ] )->pluck($param)->first();
    }
}

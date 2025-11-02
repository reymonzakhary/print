<?php

namespace Modules\Cms\Foundation\Traits;

use Modules\Cms\Entities\Resource;

trait IsGeneralSnippet
{
    
    /** 
     * @param $request
     * @return self
     * build the object with request inctace to handle the login request
    */
    public static function build($request)
    {
        $instance = new self;
        $instance->request = $request;
        return $instance;
    }

    protected function getCurrentResource():Resource
    {
        $uri = '/'. request()->uri;
        if (in_array($uri, ['/', null], true)) {
            $uriQuery = function ($q) {
                $q->where('uri', '/home')->orWhere('uri', '/');
            };
        } else {
            $uriQuery = ['uri' => $uri];
        }

        return Resource::withCount('groups')->with('media', 'resourceType')->where(['language' => app()->getLocale()])->where($uriQuery)->first();
    }
}

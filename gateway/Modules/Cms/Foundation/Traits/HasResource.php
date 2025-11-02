<?php

namespace Modules\Cms\Foundation\Traits;

use Modules\Cms\Entities\Resource;

trait HasResource
{
    /**
     * get resource from the template depends on the resource id syntax
     * [[+[[id=146]]]]
     *
     * @param [type] $template
     * @return Resource|null
     */
    protected function getResourceFromTemplateId($template): Resource|null
    {
        // get add to cart resource
        preg_match_all('/\[\[\+\[\[id=(.*?)\]\]\]\]/sm', $template, $params);
        $resource_id = optional(optional($params)[1])[0];
        return $resource_id? Resource::where('resource_id', $resource_id)->first(): null;
    }

    protected function replaceTemplateIdWithResourceUrl($template) {
        $content = preg_replace_callback('/\[\[\+\[\[id=(.*?)\]\]\]\]/', function($match) {
            $resource_id = optional($match)[1];
            return $resource_id? Resource::where('resource_id', $resource_id)->first()?->uri: '';
        }, $template);
        return $content;
    }
}

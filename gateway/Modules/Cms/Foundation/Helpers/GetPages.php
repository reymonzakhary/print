<?php

namespace Modules\Cms\Foundation\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;

class GetPages extends SnippetContract
{
    use IsGeneralSnippet;

    /**
     * returns the html content for the compiler to append
     *
     * @return string|null
     */
    public function getChunk(): string|null
    {
        $outerChunk = DB::table('chunks')->where('name', $this->outerChunk)->first();
        $innerChunk = DB::table('chunks')->where('name', $this->innerChunk)->first();
        
        $resources = $this->getResourcesFromDatabase();
        $content = '';

        $resources?->each(function ($resource) use (&$content, $outerChunk, $innerChunk) {
            
            $resourceContents = collect($resource->content)->pluck('value', 'key')
                ->merge($resource->only('title', 'intro_text', 'long_title', 'menu_title', 'uri'))
                ->toArray();


            if (!$this->accepted($this->include_self) && !$this->pages){
                $chunk = $innerChunk->content;
            } else {
                $chunk = $outerChunk->content;
            }

            $content .= $this->replaceKeysInChunk(htmlspecialchars_decode($chunk), $resourceContents);
            if (!$this->accepted($this->pages)){
                $content .= $this->renderChildren($resource->children, $innerChunk);
            }
        });
        return htmlspecialchars_decode($content);
    }

    /**
     * applies the order by
     *
     * @return array
     */

    /**
     * Render children of the resource 
     *
     * @param [type] $children
     * @param [type] $chunk
     * @return string
     */
    private function renderChildren($children, $chunk):string
    {
        if (!$children || !$chunk) { return ''; }

        $content = '';

        $children->each(function ($child) use (&$content, $chunk) {
            $resourceContents = collect($child->content)->pluck('value', 'key')->merge($child->only('title', 'intro_text', 'long_title', 'menu_title', 'uri'))->toArray();
            $content .= $this->replaceKeysInChunk(htmlspecialchars_decode($chunk->content), $resourceContents);
        });
        return $content;
    }


    /**
     * retrieve resources as tree if needed
     *
     * @return Collection|null
     */
    private function getResourcesFromDatabase(): Collection|null
    {
        $resources = Resource::orderBy($this->applyOrderBy()[0], $this->applyOrderBy()[1])
            ->where('resource_id', $this->start)
            ->where('language', app()->getLocale());

        if ($this->start && !$this->accepted($this->include_self) && !$this->pages) {
            $resources = $resources->with(['children' => fn ($q) => $q->where('language', app()->getLocale())->limit($this->take??15)])->first()->children;

        } else if ($this->start && $this->accepted($this->include_self) && !$this->pages) {
            $resources = $resources->with(['children' => fn ($q) => $q->where('language', app()->getLocale())->limit($this->take??15)])->get();

        } else if ($this->pages) {
            $resources = Resource::orderBy($this->applyOrderBy()[0], $this->applyOrderBy()[1])->whereIn('resource_id', explode(',', $this->pages))
                ->where('language', app()->getLocale())
                ->without([
                    'children'
                ])->get();
        } else {
            $resources = null;
        }
        return $resources;

    }

    private function applyOrderBy(): array
    {
        $exploded = explode(',', $this->orderBy);
        if (count($exploded) === 2 && in_array($exploded[0], ['id', 'resource_id']) && in_array($exploded[1], ['asc', 'desc'])){
            return $exploded;
        } else {
            return ['id', 'desc'];
        }
    }

    /**
     * replaces the keys in of the chunk
     *
     * @param [type] $content
     * @param [type] $replacements
     * @return void
     */
    private function replaceKeysInChunk($content, $replacements)
    {
        return preg_replace_callback('/\[\[\+(.*?)\]\]/m', function($match) use ($replacements) {
            return optional($replacements)[$match[1]];
        }, $content);
    }
}

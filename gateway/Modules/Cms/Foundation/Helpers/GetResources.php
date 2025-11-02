<?php

namespace Modules\Cms\Foundation\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Cms\Entities\Chunk;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Foundation\Compiler\SyntaxAnalyzer;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;

class GetResources extends SnippetContract
{
    use IsGeneralSnippet;

    /**
     * returns the html content for the compiler to append
     *
     * @return string|null
     */
    public function getChunk(): string|null
    {
        $this->formatInputs();
        return $this->render();
    }

    public function formatInputs()
    {
        // formate and filter the sortby
        if (!in_array($this->sortby, ['id', 'created_at', 'resource_id', 'base_id', 'uri', 'slug', 'title', 'long_title', 'intro_text', 'description', 'menu_title', 'created_by'])){
            $this->sortby = 'id';
        }

        // format the sortdir to be within [ASC, DESC]
        if (!in_array($this->sortdir, ['ASC', 'DESC'])) {
            $this->sortdir = 'ASC';
        } else if (in_array($this->sortdir, ['asc', 'desc'])) {
            $this->sortdir = strtoupper($this->sortdir);
        }
        // format depth if not numeric
        if ($this->depth && !is_numeric($this->depth)) {
            $this->depth = 1;
        }

        $this->resources = collect(explode(',', $this->resources))->reject(fn ($id) => empty($id));
        $this->excludes = $this->resources->filter(fn ($id) => str_contains($id, '-'))->toArray();
        $this->whitelist = $this->resources->filter(fn ($id) => !str_contains($id, '-'))->toArray();

        $this->parents = explode(',', $this->parents);

        $this->tpl = Cache::get(tenant()->uuid.'.chunk.'.$this->tpl)?? Chunk::where('name', $this->tpl)->first();
    }

    /**
     * retrieve resources as tree if needed
     *
     * @return Collection|null
     */
    private function render()
    {
        $parent_id = $this->parents[0];

        if ($parent_id === '-1') {
            $resources = Resource::with('media')->whereIn('resource_id', $this->resources)->where('language', app()->getLocale())->orderBy($this->sortby, $this->sortdir)->get();
            $resources = $resources->map(fn($r) => $this->visit($r));
            return htmlspecialchars_decode($resources->reduce(function (?string $carry, ?string $item) {
                return $carry . $item . "\n";
            }));
        }

        $parent_id = trim(str_replace('-', '', $this->parents[0]));
        $resource = $this->getTree(
            fn ($q) => $q->where('resource_id', $parent_id),
            $this->sortby,
            $this->sortdir,
            $this->depth
        );

        return $this->breadthFirstTraversing([$resource->first()]);
    }

    private function visit(Resource $node)
    {
        if (in_array('-'. $node->resource_id, $this->parents)){
            return null;
        }

        if (in_array('-'. $node->resource_id, $this->excludes))
        {
            return null;
        }

        if (!empty($this->whitelist) && !in_array($node->resource_id, $this->whitelist)) {
            return null;
        }

        // parse and render the cms syntax inside the chunk
        $parser = new SyntaxAnalyzer($node);

        return $parser->injectResource(
            $this->tpl->content
        )->parse()->getHtml();
    }

    function breadthFirstTraversing(array $queue, array $output = [])
    {
        // If the queue is empty, return the output.
        if (count($queue) === 0) {
            // dd($output, $this->parents, $this->resources);
            return implode("\n", $output);
        }

        // Take the first item from the queue and visit it.
        $node = array_shift($queue);

        $output[] = $this->visit($node);

        // Add any children to the queue.
        foreach ($node->children ?? [] as $child) {
            $queue[] = $child;
        }

        // Repeat the algorithm with the rest of the queue.
        return $this->breadthFirstTraversing($queue, $output);
    }

    public function childrenTreeQuery($parent)
    {
        return function($q) use ($parent) {
            return $q->whereIn('id');
        };
    }

    public function getTree($constraint, $sortby = null, $sortdir = null, $depth = null)
    {
        return Resource::with('media')->orderBy($sortby, $sortdir)->where([
            ['language', app()->getLocale()],
            ['hidden', false]
        ])->treeOf($constraint, $depth)->get()->toTree();
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

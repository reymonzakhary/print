<?php

namespace Modules\Cms\Foundation\Helpers;

use Illuminate\Support\Facades\DB;
use Modules\Cms\Entities\Chunk;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Foundation\Compiler\SyntaxAnalyzer;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;

class Menu extends SnippetContract
{

    use IsGeneralSnippet;

    private $html = '';

    /**
     * returns the content
     *
     * @return void
     */
    public function getChunk()
    {
        $this->bootstrapChunks();
        $this->bootstrapResourcesTree();

        return $this->renderMenu();
    }

    protected function renderMenu(): string
    {
        $this->tree->map(function ($rootNode) {
            $this->html .= '#resource_id-'.$rootNode->resource_id;
            return $this->breadthFirstTraversing([$rootNode]);
        });

        $this->html = str_replace('[[+m.wrapper]]', $this->html, $this->chunks->firstWhere('name', $this->wrapperChunk)?->content??'');
        return htmlspecialchars_decode($this->html);
    }

    protected function bootstrapChunks(): void
    {
        $this->chunks = Chunk::whereIn('name', [$this->wrapperChunk, $this->rowChunk, $this->innerWrapperChunk, $this->innerRowChunk])->get();
    }

    protected function bootstrapResourcesTree(): void
    {
        $this->tree = Resource::with('media')->orderBy('sort')->where([
            ['language', app()->getLocale()],
            ['published', true],
            ['hidden', false]
        ])->tree()->get()->toTree();
    }

    private function visit(Resource $node)
    {
        // return $node->menu_title;
        $level = $this->level($node);

        $parser = new SyntaxAnalyzer($node);

        $html = $parser->injectResource(
            $this->getChunkByLevel(
                $level
            )?->content
        )->parse()->getHtml();

        $this->html = str_replace('#resource_id-'.$node->resource_id, $html, $this->html);

        if ($node->children->isNotEmpty()) {
            $wrapper = $this->chunks->firstWhere('name', $this->innerWrapperChunk)?->content;

            $placeholder = implode("\n", $node->children->map(function ($child) {
                return '#resource_id-' . $child->resource_id;
            })->toArray());

            $wrapper = str_replace('[[+m.wrapper]]', $placeholder, $wrapper);

            $this->html = str_replace('[[+m.wrapper]]', $wrapper, $this->html);
        } else {
            $this->html = str_replace('[[+m.wrapper]]', '', $this->html);
        }

        return $node->resource_id;
    }

    function breadthFirstTraversing(array $queue, array $output = [])
    {
        // If the queue is empty, return the output.
        if (count($queue) === 0) {
            // should apply wrapper here
            return preg_replace_callback('/\[\[\+wrapper\]\]/sm', function ($match) use ($output) {
                return implode("\n", $output);
            }, $this->chunks->firstWhere('name', $this->innerWrapperChunk??$this->wrapperChunk)->content??'');
            return implode(', ', $output);
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

    private function level($node)
    {
        return count(explode('.', $node?->path));
    }

    private function getChunkByLevel(int $level)
    {
        return match ($level) {
            1 => $this->chunks->firstWhere('name', $this->rowChunk),
            default => $this->chunks->firstWhere('name', $this->innerRowChunk??$this->rowChunk)
        };
    }

}

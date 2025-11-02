<?php

namespace Modules\Cms\Foundation\Helpers;

use Illuminate\Support\Facades\DB;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Enums\ProductTypesEnum;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;
use App\Http\Resources\Categories\CategoryResource;
use App\Http\Resources\Categories\PrintBoopsResource;
use Modules\Cms\Enums\BlockTypesEnum;
use Modules\Cms\Foundation\Compiler\SyntaxAnalyzer;
use Modules\Cms\Foundation\Traits\HasCategory;
use Modules\Cms\Foundation\Traits\HasDirectives;
use Modules\Cms\Foundation\Traits\HasProductImages;
use Modules\Cms\Foundation\Traits\HasResource;

class Category extends SnippetContract
{
    use IsGeneralSnippet, HasResource, HasDirectives, HasProductImages, HasCategory;

    private $category = null;

    public function __construct()
    {
        parent::__construct();
        $this->resource = $this->getCurrentResource();
        $category = optional(collect($this->resource->content)->firstWhere('type', BlockTypesEnum::CATEGORY->value))['value'];
        if ($category){
            $category = $this->getBlockCategory($category);
            $this->category = $category;
        }
    }

    /**
     * returns the html content for the compiler to append
     *
     * @return string|null
     */
    public function getChunk()
    {
        $tpl = htmlspecialchars_decode($this->getChunkFromCacheOrDB($this->tpl)?->content);

        $key = optional(collect($this->resource->content)->firstWhere('type', BlockTypesEnum::CATEGORY->value))['key'];
        return match ($key) {
            'boops' => $this->renderBoops($tpl),
            'category' => $this->renderProduct($tpl),
            default => ''
        };
    }

    /** 
     * @param mixed $tpl
     * @return string
    */
    private function renderBoops($tpl): string
    {
        return collect(optional($this->category)['boops'])->map(function ($boop) use ($tpl) {
            return (new SyntaxAnalyzer($boop))->injectModel($tpl)->parse()->getHtml();
        })->join("\n");
    }

    /** 
     * @param mixed $tpl
     * @return string
    */
    private function renderProduct($tpl): string
    {
        return collect($this->category['products'])->map(function ($product) use ($tpl) {
            return (new SyntaxAnalyzer($product))->injectModel($tpl)->parse()->getHtml();
        })->join("\n");
    }

}

<?php

namespace Modules\Cms\Foundation\Helpers;

use Modules\Cms\Enums\BlockKeysEnum;
use Modules\Cms\Enums\ProductTypesEnum;
use Modules\Cms\Foundation\Compiler\SyntaxAnalyzer;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\HasCategory;
use Modules\Cms\Foundation\Traits\HasRecursiveModels;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;

class CustomCategory extends SnippetContract
{
    use IsGeneralSnippet, HasRecursiveModels, HasCategory;

    public function __construct()
    {
        parent::__construct();
        $this->resource = $this->getCurrentResource();
        $category = optional(collect($this->resource->content)->filter(fn ($item) => $item['key'] == BlockKeysEnum::CATEGORY->value)->first())['value'];

        if ($category){
            $category = $this->getBlockCategory($category);
            $this->category = $category;
        }
    }

    /**
     * @return string
     */
    public function getChunk()
    {
        if ($this->resource->resourceType->name !== ProductTypesEnum::PRODUCT->value || !$this->category) {
            return '';
        }

        $tpl = $this->getChunkFromCacheOrDB($this->tpl)?->content;

        return (new SyntaxAnalyzer($this->category))->injectModel($tpl)->resolve()->getHtml();
    }
}

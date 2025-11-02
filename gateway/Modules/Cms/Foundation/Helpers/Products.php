<?php

namespace Modules\Cms\Foundation\Helpers;

use Illuminate\Support\Facades\DB;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Enums\ProductTypesEnum;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;
use App\Http\Resources\Categories\CategoryResource;
use App\Http\Resources\Categories\PrintBoopsResource;
use Modules\Cms\Foundation\Traits\HasDirectives;
use Modules\Cms\Foundation\Traits\HasProductImages;
use Modules\Cms\Foundation\Traits\HasResource;

class Products extends SnippetContract
{
    use IsGeneralSnippet, HasResource, HasDirectives, HasProductImages;

    public function __construct()
    {
        parent::__construct();
        $this->resource = $this->getCurrentResource();
    }

    /**
     * returns the html content for the compiler to append
     *
     * @return string|null
     */
    public function getChunk(): string|null
    {
        $chunks = DB::table('chunks')->whereIn('name', [$this->wrapper, $this->product])->get();
        $wrapper = htmlspecialchars_decode($chunks->where('name', $this->wrapper)->first()?->content??'');
        $productChunk = htmlspecialchars_decode($chunks->where('name', $this->product)->first()?->content??'');

        $products = $this->getResourceProducts();

        $content = htmlspecialchars_decode(str_replace('[[+products]]', $this->wrapProducts($products, $productChunk), $wrapper));

        return $content??'';
    }

    private function getResourceProducts()
    {
        $resource = $this->resource_id?
            Resource::where('id', $this->resource_id):
            Resource::where('uri', '/'.request()->path());

        $resource = $resource->with(['resourceType' => fn ($q) => $q->where('name', ProductTypesEnum::PRODUCT->value)])->first();

        if ($resource->isCustomCategory()) {
            $category = CategoryResource::make(\App\Models\Tenants\Category::where('id', $resource->category)->with('products')->first());
            $products = $category->products;
        } else if ($resource->isCustomCategory() && $resource->category) {
            $category = PrintBoopsResource::make($this->boopsService->obtainCategoryBoops($resource->category));
        }

        return $products?? collect();
    }

    private function wrapProducts($products, $chunk)
    {

        $chunk = $this->replaceTemplateIdWithResourceUrl($chunk);

        $content = '';

        foreach ($products as $product) {
            $template = $chunk;

            $template = preg_replace_callback('/\[\[\+media\?(.*?)]]/', function ($match) use ($product) {
                if (!$this->accepted($match[1])){
                    return json_encode($product->media->map(fn($item) => $this->formatProductImages($item)));
                }
                $params = $this->params($match[1]); // get parameters from a string

                $content = $this->getChunkFromCacheOrDB(optional($params)['tpl'])?->content;

                return $product->media->map(fn($fm) => $this->renderProductImages($fm, $content))->reduce(fn ($carry, $item) => "\n" . $item);
            }, $template);

            $template = preg_replace_callback('/\[\[\+(\w+)]]/', function($match) use ($product) {
                $identifier = $match[1];
                return match ($identifier) {
                    'price' => $product->sku->price->format(),
                    'id' => $product->row_id,
                    default => $product->{$identifier}
                };
            }, $template);

            $template = $this->replaceSpecificDirective('addToCart', $template, [
                'product_id' => $product->row_id,
                'callback_uri' => $this->resource->uri,
            ]);
            $content .= $template;
        }
        return $content;
    }

}

<?php

namespace Modules\Cms\Foundation\Helpers;


use App\Foundation\Settings\Settings;
use App\Models\Tenants\CartVariation;
use App\Plugins\Moneys;
use Illuminate\Support\Facades\Cache;
use Modules\Cms\Foundation\Cart\Contracts\CartContractInterface;
use Modules\Cms\Foundation\Compiler\Compiler;
use Modules\Cms\Foundation\Compiler\SyntaxAnalyzer;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\HasCategory;
use Modules\Cms\Foundation\Traits\HasCustomHtmlAttributes;
use Modules\Cms\Foundation\Traits\HasDirectives;
use Modules\Cms\Foundation\Traits\HasProductImages;
use Modules\Cms\Foundation\Traits\HasResource;
use Modules\Cms\Foundation\Traits\InteractsWithCart;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;

class Cart extends SnippetContract
{
    use HasDirectives, HasCustomHtmlAttributes, IsGeneralSnippet, HasResource, HasProductImages, HasCategory, InteractsWithCart;

    private $cartInterface;
    private $currentResource;
    private $cartContents;

    public function __construct() {
        parent::__construct();

        $this->cartInterface = app(CartContractInterface::class);

        if (!Cache::has('cartContents')) {
            Cache::put('cartContents', $this->cartInterface->contents(), 3600);
        }

        $this->cartContents = Cache::get('cartContents')?? collect();
        $this->currentResource = $this->getCurrentResource();
    }

    /**
     * @param void
     * @return string
    */
    public function getChunk()
    {
        $chunks = collect([
            $this->getChunkFromCacheOrDB($this->productChunk),
            $this->getChunkFromCacheOrDB($this->printProductChunk),
            $this->getChunkFromCacheOrDB($this->wrapperChunk),
            $this->getChunkFromCacheOrDB($this->emptyChunk),
        ]);

        $directiveData = ['callback_url' => $this->getCurrentResource()->uri];
        $wrapperChunk = $this->replaceSpecificDirective(
            'checkout',
            $this->replaceSpecificDirective(
                'emptyCart',
                htmlspecialchars_decode($chunks->where('name', $this->wrapperChunk)->first()?->content??''),
                $directiveData
            ),
            [
                'callback_uri' => $this->currentResource->uri,
                'onSuccessRedirect' => $this->onSuccessRedirect,
            ]
        );

        if ($this->cartContents->count()) {
            $productChunk = htmlspecialchars_decode($chunks->where('name', $this->productChunk)->first()?->content??'');
            $printProductChunk = htmlspecialchars_decode($chunks->where('name', $this->printProductChunk)->first()?->content??'');

            $products = $this->getItemsContent($productChunk, $printProductChunk);
            $content = str_replace('[[+products]]', $products, $wrapperChunk);
        } else {
            $content = htmlspecialchars_decode($this->getChunkFromCacheOrDB($this->emptyChunk)?->content??'');
            $content = str_replace('[[+products]]', $content, $wrapperChunk);
        }

        $content = str_replace('[[+subtotal]]', $this->subtotal(), $content);
        $content = str_replace('[[+totalVat]]', $this->totalVat(), $content);
        $content = str_replace('[[+total]]', $this->total(), $content);
        $content = str_replace('[[+cartCount]]', $this->cartContents->count(), $content);
        return (new Compiler($this->currentResource))->compileHelperClasses($this->replaceTemplateIdWithResourceUrl($content));
    }

    private function getItemsContent($customProductChunk, $printProductChunk)
    {
        $content = '';
        foreach ($this->cartContents as $item) {
            if ($this->accepted($item->sku)) {
                $content .= $this->renderCustomProductItem($item, $customProductChunk);
            } else {
                $content .= $this->renderPrintProductItem($item, $printProductChunk);
            }
        }
        return $content;
    }

    /**
     *
     * @param cartVariation $item
     * @param string $chunk
     * @return string
     */
    private function renderCustomProductItem($item, $chunk): string
    {
        $chunk = str_replace('[[+name]]', $item->sku?->product?->name, $chunk);
        $chunk = str_replace('[[+qty]]', $item->qty, $chunk);
        $chunk = str_replace('[[+total]]', $item->price->format(), $chunk);

        $chunk = $this->printProductData($item->sku?->product,
            $this->getProductImagesHtml($item->sku?->product, $chunk)
        );

        $chunk = $this->replaceSpecificDirective('removeFromCart', $chunk, [
            'item_id' => $item->id,
            'callback_url' => $this->currentResource->uri
        ]);

        return (new SyntaxAnalyzer(['item' => $item->toArray()]))->injectModel($chunk)->resolve()->getHtml();
    }

    /**
     *
     * @param cartVariation $item
     * @param string $chunk
     * @return string
     */
    private function renderPrintProductItem($item, $chunk): string
    {
        $chunk = $this->replaceSpecificDirective('removeFromCart', $chunk, [
            'item_id' => $item->id,
            'callback_url' => $this->currentResource->uri
        ]);

        $chunk = str_replace('[[+qty]]', $item->qty, $chunk);

        return (new SyntaxAnalyzer(['item' => $item->toArray()]))->injectModel($chunk)->resolve()->getHtml();
    }

    /**
     *
     * @return int
     */
    private function customProductsSubtotal()
    {
        return $this->cartContents->where('sku_id', '!=', null)->sum(fn ($cv) => $cv->qty * $cv->price->amount());
    }

    /**
     *
     * @return int
     */
    private function printProductsSubtotal()
    {
        return $this->cartContents->where('sku_id', null)->sum(fn ($cv) => optional(optional($cv->variation)['prices'])['tables']['p']);
    }

    /**
     *
     * @return Moneys
     */
    public function subtotal(): Moneys
    {
        return (new Moneys())->setAmount($this->customProductsSubtotal() + $this->printProductsSubtotal());
    }

    /**
     *
     * @return Moneys
     */
    public function totalVat(): Moneys
    {
        return (new Moneys())->setAmount($this->cartContents->sum(function ($cv) {
            return
                $cv->sku_id? (Settings::vat() * $cv->price->amount()) / 100
                : (Settings::vat() * optional(optional($cv->variation)['prices'])['tables']['p']) / 100;
        }));
    }

    /**
     *
     * @return Moneys
     */
    public function total(): Moneys
    {
        return (new Moneys())->setAmount($this->subtotal()->amount() + $this->totalVat()->amount());
    }


    /**
     *
     * @param $product
     * @param $template
     *
     * @return string
     */
    public function getProductImagesHtml($product, $template)
    {
        return preg_replace_callback('/\[\[\+product\.media\?(.*?)]]/', function ($match) use ($product) {
            if (!$this->accepted($match[1])){
                return json_encode($product->media->map(fn($item) => $this->formatProductImages($item)));
            }
            $params = $this->params($match[1]); // get parameters from a string

            $content = $this->getChunkFromCacheOrDB(optional($params)['tpl'])?->content;

            return htmlspecialchars_decode($product?->media->map(fn($fm) => $this->renderProductImages($fm, $content))->reduce(fn ($carry, $item) => "\n" . $item));
        }, $template);
    }


    /**
     *
     * @param mixed $product
     * @param mixed $itemChunk
     *
     * @return string
     */
    public function printProductData($product, $itemChunk)
    {
        return preg_replace_callback('/\[\[\+product\.(\w+)]]/', function($match) use ($product) {
            $identifier = $match[1];
            return match ($identifier) {
                'price' => $product->sku->price->format(),
                'id' => $product->row_id,
                default => $product->{$identifier}
            };
        }, $itemChunk);
    }
}

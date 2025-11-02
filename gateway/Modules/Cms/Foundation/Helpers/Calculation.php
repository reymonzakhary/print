<?php

namespace Modules\Cms\Foundation\Helpers;

use App\Services\Categories\Products\Prices\PriceService;
use App\Services\Suppliers\SupplierCategoryService;
use Modules\Cms\Foundation\Contracts\SnippetContract;
use Modules\Cms\Foundation\Traits\IsGeneralSnippet;
use Modules\Cms\Foundation\Compiler\SyntaxAnalyzer;
use Modules\Cms\Foundation\Traits\HasCategory;
use Modules\Cms\Foundation\Traits\HasDirectives;
use Modules\Cms\Foundation\Traits\HasProductImages;
use Modules\Cms\Foundation\Traits\HasResource;
use App\Services\Tenant\Calculations\CalculationService;
use Modules\Cms\Transformers\Snippets\Calculation\FullCalculationResource;

class Calculation extends SnippetContract
{
    use IsGeneralSnippet, HasResource, HasDirectives, HasProductImages, HasCategory;

    private $product = [];

    private $quantity = null;

    private CalculationService $calculationService;

    private SupplierCategoryService $supplierCategoryService;

    private PriceService $priceService;


    public function __construct()
    {
        parent::__construct();

        $this->product = request()->query('product');
        $this->quantity = request()->query('quantity');
        $this->category = request()->query('category');
        $this->calculationService = new CalculationService();
        $this->supplierCategoryService = new SupplierCategoryService();
        $this->priceService = new PriceService();
    }

    /**
     * returns the html content for the compiler to append
     *
     * @return string|null
     */
    public function getChunk()
    {
        $category = $this->getBlockCategory($this->category);
        try {
            if (!empty($this->product)) {
                $tpl = (new SyntaxAnalyzer($this->getCurrentResource()))
                    ->injectResource(htmlspecialchars_decode(
                        $this->replaceSpecificDirective(
                            'addToCart',
                            $this->getChunkFromCacheOrDB($this->tpl)?->content,
                            [
                                'callback_uri' => request()->fullUrl(),
                                'category_id' => optional($category)['id'],
                                'category_name' => optional($category)['name'],
                                'category_slug' => optional($category)['slug'],
                            ]
                        )))
                    ->resolve()
                    ->getHtml();

                $result = $this->calculate();

                return (new SyntaxAnalyzer([
                    'calculation' => $result,
                    'message' => optional($result)['message']
                ]))->injectModel($tpl)
                    ->parse()
                    ->getHtml();
            }
        } catch (\Throwable $th) { }
        
    }

    /**
     * 
     * @return array
     */
    public function calculate()
    {
        $cat = $this->supplierCategoryService->obtainCategoryObject($this->category);

        if (optional(optional($cat)['price_build'])['full_calculation']) {
            $result = $this->calculationService->obtainCalculatedPrices($this->category, [
                'product' => $this->product,
                'quantity' => $this->quantity,
                'tenant' => tenant(),
                'uuid' => tenant()->uuid
            ]);

            return FullCalculationResource::collection($result)->resolve();
        }

        if (optional(optional($cat)['price_build'])['semi_calculation']) {
            $result = $this->calculationService->obtainSemiCalculatedShopPrices($this->category, [
                'product' => $this->product,
                'quantity' => $this->quantity,
                'tenant' => tenant(),
                'uuid' => tenant()->uuid
            ]);

            return FullCalculationResource::collection($result)->resolve(); // @todo create resource for semi calculation
        }
        
        // @todo complete with collection calculation
        return [];
    }

}

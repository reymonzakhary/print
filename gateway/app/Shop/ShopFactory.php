<?php

namespace App\Shop;

use App\Http\Controllers\Tenant\Mgr\Categories\CategoryController;
use App\Http\Controllers\Tenant\Mgr\Custom\Categories\CategoryController as CustomCategoryController;
use App\Models\Tenants\Category;
use Illuminate\Http\Request;

class ShopFactory
{
    protected ?int $category;

    public function __construct(public Request $request) {}

    public function custom()
    {
        $category = $this->request->category;
        $product = $this->request->product;
        $blueprint = [];
        if ($this->request->isMethod('GET')) {
            return match ($product) {
                null => match ($category) {
                    // get all
                    null => app(CustomCategoryController::class)->index($this->request),
                    $category => app(CustomCategoryController::class)->show(Category::find($category)),
                    default => dd('error')
                },
                $product => dd('method'),
                default => dd('error')
            };
        }
//        match($product) {
//            null => match($category) {
//                null, $category => dd(' post error'),
//                default => dd('error')
//            },
//            $product => dd( 'product method'),
//            default => dd('error')
//
//        };

        // GET has category id and not product

        // get one
        //
        // has product
        // get product
        //
        // POST check blueprint if needed
        // response
        // approve
        // post to cart checkout
    }

    public function print()
    {
        $category = $this->request->category;
        $product = $this->request->product;
        $blueprint = [];
        if ($this->request->isMethod('GET')) {
            return match ($product) {
                null => match ($category) {
                    // get all
                    null => app(CategoryController::class)->index($this->request),
                    $category => app(CategoryController::class)->show($category),
                    default => dd('error')
                },
                $product => dd('method'),
                default => dd('error')
            };
        }
    }

    public function handelCustom()
    {

    }
}

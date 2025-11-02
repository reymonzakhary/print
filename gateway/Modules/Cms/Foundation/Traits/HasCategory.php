<?php

namespace Modules\Cms\Foundation\Traits;


use App\Services\Categories\BoopsService;
use App\Services\Suppliers\SupplierCategoryService;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Transformers\Snippets\GetResources\CategoryResource;
use Modules\Cms\Transformers\Snippets\GetResources\PrintBoopsResource;
use Symfony\Component\HttpFoundation\Response;

trait HasCategory
{
    /** 
     * @param mixed $category custom category id or print category slug
     * @return array|null
    */
    private function getBlockCategory($category)
    {
        if (is_numeric($category)) {
            return CategoryResource::make(\App\Models\Tenants\Category::where('id', $category)->with('media', 'products.media')->first())->resolve();
        } else if (!is_numeric($category) && $category) {

            $obtainedCategory = (new SupplierCategoryService())->obtainCategoryObject($category);
            if (optional($obtainedCategory)['status'] == Response::HTTP_NOT_FOUND) {
                return;
            }

            $obtainedBoops = (new BoopsService())->obtainCategoryBoops($category);
            if (optional($obtainedCategory)['status'] == Response::HTTP_NOT_FOUND) {
                return;
            }

            $obtainedCategory['boops'] = optional($obtainedBoops)[0]['boops'];
            
            return PrintBoopsResource::make($obtainedCategory)->resolve();
        }
    }
}
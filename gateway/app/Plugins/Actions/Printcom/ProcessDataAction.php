<?php

declare(strict_types=1);

namespace App\Plugins\Actions\Printcom;

use App\Plugins\Abstracts\PluginActionAbstract;
use App\Plugins\Concrete\PluginActionContractInterface;
use App\Services\Suppliers\SupplierCategoryService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\App;

final class ProcessDataAction extends PluginActionAbstract implements PluginActionContractInterface
{
    /**
     * @throws GuzzleException
     */
    public function handle(): void
    {
        foreach ($this->from['categoriesData'] as $categoryData) {
            App::make(SupplierCategoryService::class)->handleExternalCategoryData($categoryData);
        }
    }
}

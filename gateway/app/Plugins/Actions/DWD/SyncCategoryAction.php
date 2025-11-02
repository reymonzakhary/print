<?php

namespace App\Plugins\Actions\DWD;

use App\Plugins\Abstracts\PluginActionAbstract;
use App\Plugins\Concrete\PluginActionContractInterface;

class SyncCategoryAction  extends PluginActionAbstract implements PluginActionContractInterface
{

    public function handle()
    {
        try {
            // Get the categories data from the previous action
            $categoriesData = $this->from['categories'] ?? [];
            
            if (empty($categoriesData)) {
                $this->output = ['categories' => [], 'error' => 'No data to import'];
                return;
            }

            // Call the import endpoint with the fetched data
            $response = $this->makeRequest(
                method: 'POST',
                requestUrl: "/import",
                formParams: array_merge([
                    'tenant_id' => $this->request->tenant->uuid,
                    'tenant_name' => $this->configRepository->hostname->fqdn,
                    'vendor' => $this->configRepository->getPluginName(),
                ], $categoriesData),
                forceJson: true
            );

            $this->output = [
                'categories' => $response,
                'imported_count' => count($categoriesData),
                'status' => 'success'
            ];

        } catch (\Exception $e) {
            $this->output = [
                'categories' => [],
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }
}

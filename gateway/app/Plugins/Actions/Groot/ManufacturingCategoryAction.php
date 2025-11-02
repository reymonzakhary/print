<?php

namespace App\Plugins\Actions\Groot;

use App\Plugins\Abstracts\PluginActionAbstract;
use App\Plugins\Concrete\PluginActionContractInterface;
use Illuminate\Support\Arr;

class ManufacturingCategoryAction  extends PluginActionAbstract implements PluginActionContractInterface
{

    public function handle()
    {
        // Get SKUs and normalize to array
        $skus = $this->request->get('skus');

        // Normalize to array if needed
        if (!is_array($skus)) {
            $skus = $skus ? [$skus] : [];
        }

        $payload = [
            'tenant_id' => $this->request->tenant->uuid,
            'tenant_name' => $this->configRepository->hostname->fqdn,
            'vendor' => $this->configRepository->getPluginName(),
            'skus' => $skus,
            'limit' => $this->request->get('limit'),
        ];

        $response = $this->makeRequest(
            method: 'POST',
            requestUrl: "sync",
            formParams: $payload,
            forceJson: true
        );

        // Now the response should always have a 'data' key
        $data = $response['data'] ?? [];
        $meta = Arr::except($data, [0]);
        $category = array_merge($meta, $data[0] ?? []);

        // Store the fetched data in the pipeline for the next action
        $this->output = [
            'categories' => $category,
            'raw_response' => $response,
            'status' => 'success'
        ];

        // Also set the from property to ensure data flows correctly
        $this->from = [
            'categories' => $category
        ];
    }
}

<?php

namespace App\Plugins\Actions\Cartim;

use App\Plugins\Abstracts\PluginActionAbstract;
use App\Plugins\Concrete\PluginActionContractInterface;
use Illuminate\Support\Arr;

class ManufacturingCategoryAction  extends PluginActionAbstract implements PluginActionContractInterface
{

    public function handle()
    {
        $category = collect($this->request->get('skus'))->first();

        // Ensure payload is consistent and non-empty
        $skus = $this->request->get('skus');
        if (!is_array($skus)) {
            $skus = $skus ? [$skus] : [];
        }
        if (empty($skus) && $category) {
            $skus = [$category];
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

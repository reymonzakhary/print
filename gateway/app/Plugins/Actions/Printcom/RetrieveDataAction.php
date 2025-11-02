<?php

declare(strict_types=1);

namespace App\Plugins\Actions\Printcom;

use App\Plugins\Abstracts\PluginActionAbstract;
use App\Plugins\Concrete\PluginActionContractInterface;
use App\Plugins\Util\Printcom\ResponseValidator;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\App;

final class RetrieveDataAction extends PluginActionAbstract implements PluginActionContractInterface
{
    /**
     * @throws GuzzleException
     */
    public function handle(): void
    {
        $response = $this->makeRequest(
            method: 'POST',
            requestUrl: '/sync',
            formParams: [
                'tenant_id' => $this->request->tenant->uuid,
                'tenant_name' => $this->configRepository->hostname->fqdn,
                'vendor' => $this->configRepository->getPluginName(),
                'skus' => $this->request->get('skus'),
                'limit' => $this->request->get('limit'),
            ],
            forceJson: true
        );

        App::make(ResponseValidator::class)->ensurePluginResponseIsValid($response);

        $this->output = ['categoriesData' => $response['data']];
    }
}

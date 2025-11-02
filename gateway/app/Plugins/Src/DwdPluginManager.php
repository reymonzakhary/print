<?php

declare(strict_types=1);

namespace App\Plugins\Src;

use App\Models\Tenants\Address;
use App\Models\Tenants\Context;
use App\Plugins\Contracts\PluginManager;
use App\Plugins\Util\DWD\DataMapper;
use App\Plugins\Util\DWD\ResponseValidator;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Exception;

final class DwdPluginManager extends PluginManager
{
    public function __construct(
        private readonly DataMapper        $dataMapper,
        private readonly ResponseValidator $responseValidator,
    )
    {
    }

    /**
     * Get the pipeline configuration for syncing
     *
     * @return array
     */
    public function getSyncPipelineConfig(): array
    {
        return [
            [
                "id" => 1,
                "uses" => "action",
                "events" => [],
                "action" => [
                    "as" => "RetrieveData",
                    "path" => "DWD",
                    "model" => "ManufacturingCategoryAction",
                    "input" => [
                        "from" => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the price for a given category, quantity, and product variations.
     *
     * @param int $quantity The quantity of the product (A.K.A., category)
     * @param array $productData The variations/selections of the product (A.K.A., category)
     * @param array $categoryData The definition data of the product (A.K.A., category)
     *
     * @return array The price data in an array format
     *
     * @throws GuzzleException
     */
    public function getPrice(
        int   $quantity,
        array $productData,
        array $categoryData,
    ): array
    {
        $mgrContext = Context::query()->findOrFail(1);
        /* @var Context $mgrContext */

        $randomAddress = $mgrContext->addresses()->firstOrFail();
        /* @var Address $randomAddress */
        $options = $this->dataMapper->mapProductPrindustryToDWD(
            $quantity,
            $productData,
            $categoryData
        );

        if(!$options) {
            return [];
        }

        $response = $this->makeRequest('POST', 'get-price',
            formParams: [
                'tenant_id' => $this->tenant_id,

                'sku' => optional($categoryData)['source_slug'],

                'options' => $options,

                'address' => array_merge(
                    $randomAddress->toArray(),
                    [
                        'country' => $randomAddress->country()->firstOrFail()->getAttribute('iso2')
                    ]
                ),
            ],

            forceJson: true
        );

        try {
            $this->responseValidator->ensurePluginResponseIsValid($response);
        } catch (Exception $e) {
            Log::debug('Failed to retrieve price from DWD', [
                'quantity' => $quantity,
                'response' => $response,
                'error' => $e->getMessage(),
                'exception_type' => get_class($e),
            ]);

            return [];
        }

        return $this->dataMapper->mapPriceFromDWDToPrindustry($quantity, $response);
    }

    /**
     * Get the categories available for the given tenant.
     *
     * @return object|array|string|null The categories data
     *
     * @throws GuzzleException
     */
    public function getCategories(): object|array|string|null
    {
        return $this->makeRequest('GET', 'categories',formParams: [
            'tenant_id' => $this->tenant_id,
        ], forceJson: true);
    }


    /**
     * Authenticate the user by making a POST request to register.
     *
     * @param array $request The request data for user registration
     *
     * @return object|array|string|null The response from the registration endpoint
     *
     * @throws GuzzleException
     */
    public function auth(
        array $request
    ): object|array|string|null
    {
        return $this->makeRequest(method: 'POST', requestUrl: 'register', formParams:  $request, forceJson: true);
    }


}

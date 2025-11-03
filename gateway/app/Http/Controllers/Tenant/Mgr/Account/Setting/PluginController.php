<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Account\Setting;

use App\Facades\Plugins;
use App\Http\Controllers\Controller;
use App\Http\Requests\Plugins\UpdateConfigurationRequest;
use App\Http\Resources\Plugins\ConfigurationResource;
use App\Http\Resources\Products\PrintProductPriceResource;
use App\Services\PluginService;
use Hyn\Tenancy\Environment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class PluginController extends Controller
{
    /**
     * Class constructor.
     *
     * @param Environment $environment The environment instance being injected.
     */
    public function __construct(
        private readonly Environment $environment
    ) {
    }

    /**
     * @return ConfigurationResource
     */
    public function index(): ConfigurationResource
    {
        return ConfigurationResource::make(
            $this->environment->tenant()->getAttribute('configure')
        )
            ->additional([
                'message' => _('Plugin retrieved successfully'),
                'status' => Response::HTTP_OK,
            ]);
    }

    /**
     * @param UpdateConfigurationRequest $request
     *
     * @return JsonResponse
     */
    public function update(
        UpdateConfigurationRequest $request,
    ): JsonResponse
    {
        $tenant = $this->environment->tenant();

        $tenant->update([
            'configure' => array_merge($tenant->getAttribute('configure')->toArray(), $request->validated())
        ]);

        return response()->json([
            'message' => _('Plugin updated successfully'),
            'status' => Response::HTTP_OK,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sync(Request $request): JsonResponse
    {
        $pluginService = Plugins::load(domain());
        $request->merge([
            'tenant_name' => domain()->fqdn,
            'tenant_id' => tenant()->uuid,
        ]);

        // Wait for the pipeline to complete
        $pluginService->bus($request, $pluginService->getSyncPipelineConfig());

        return response()->json([
            'message' => __('Data has been synced successfully'),
            'status' => Response::HTTP_OK,
        ]);

    }

    /**
     * @param Request $request
     *
     * @return PrintProductPriceResource
     */
    public function getPrice(
        Request $request
    ): PrintProductPriceResource
    {
        $pluginService = Plugins::load(domain());

        return PrintProductPriceResource::make(
            $pluginService->getPrice(
                $request->validated('sku'),
                $request->validated('quantity'),
                $request->validated('variations'),
            )
        )
            ->additional([
                'message' => __('Price data has been fetched successfully'),
                'status' => Response::HTTP_OK,
            ]);
    }

    /**
     * Get the categories from the loaded plugin service.
     *
     */
    public function categories(): mixed
    {
        $pluginService = Plugins::load(domain());

        return $pluginService->getCategories();
    }
}

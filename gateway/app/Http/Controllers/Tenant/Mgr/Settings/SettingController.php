<?php

namespace App\Http\Controllers\Tenant\Mgr\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserSettingRequest;
use App\Http\Resources\Settings\SettingResource;
use App\Models\Tenants\Setting;
use App\Scoping\Scopes\Settings\AreaScope;
use App\Scoping\Scopes\Settings\NameSpaceScope;
use App\Scoping\Scopes\Settings\SearchScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;


class SettingController extends Controller
{
    /**
     * @var int|mixed
     */
    protected int $perPage;

    /**
     * @var string
     */
    protected string $sortBy;

    /**
     * @var string
     */
    protected string $sortDir;

    /**
     * SettingController constructor.
     * @param Request $request
     */
    public function __construct(
        Request $request
    )
    {
        $this->perPage = $request->get('per_page') ?? 10;
        $this->sortBy = $request->get('sort_by') ?? 'sort';
        $this->sortDir = $request->get('sort_dir') ?? 'asc';
    }

    /**
     * @OA\Get(
     *   tags={"Settings"},
     *   path="settings",
     *   summary="list all settings",
     *   @OA\Parameter(
     *        name="namespace",
     *        in="query",
     *        required=false,
     *        description="Filter Settings by namespace",
     *        @OA\Schema(
     *            type="string"
     *        ),
     *    ),
     *   @OA\Parameter(
     *        name="area",
     *        in="query",
     *        required=false,
     *        description="Filter Settings by area",
     *        @OA\Schema(
     *            type="string"
     *        ),
     *    ),
     *   @OA\Parameter(
     *        name="search",
     *        in="query",
     *        required=false,
     *        description="Filter Settings by name",
     *        @OA\Schema(
     *            type="string"
     *        ),
     *    ),
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT"
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/SettingResource"))),
     *
     * )
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function index(): mixed
    {
        $settings = new Setting();
        return SettingResource::collection(
            $settings::permitted()->withScopes($this->scope())
                ->orderBy($this->sortBy, $this->sortDir)
                ->get()
        )->additional([
            'namespace' => $settings::permitted()->groupBy('namespace')->pluck('namespace'),
            'area' => $settings::permitted()->where('namespace', request()->get('namespace') ?? 'core')->groupBy('area')->pluck('area'),
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * @param StoreUserSettingRequest $request
     * @param Setting                 $setting
     * @return SettingResource|JsonResponse
     */
    /**
     * @OA\Put(
     *   tags={"Settings"},
     *   path="settings/{key}",
     *   summary="update account settings",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(ref="#/components/schemas/UpdateSettingRequest"),
     *    ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/SettingResource")),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function update(
        StoreUserSettingRequest $request,
        Setting                 $setting
    ): JsonResponse|SettingResource
    {
        if ($setting->update($request->validated())) {
            // TEMP SOLUTION - I believe we should have a central repository for fetching/updating
            // the settings to overcome such issues #TODO

            [$tenantUuid, $settingKey] = [
                $request->tenant->getAttribute('uuid'),
                $setting->getAttribute('key')
            ];

            # The problem is that two of the classes used to retrieve the settings are caching the value with different keys
            # See (`Foundation/Settings/Setting::class` && `Foundation/Settings/Settings::class`)

            $keysToForget = [
                sprintf('%s.%s', $tenantUuid, $settingKey),
                sprintf('%s_settings_system_%s', $tenantUuid, $settingKey),
                sprintf('%s_settings_user_%s', $tenantUuid, $settingKey),
            ];

            foreach ($keysToForget as $keyToForget) {
                Cache::forget($keyToForget);
            }

            return SettingResource::make(
                $setting->find($setting->id)
            )->additional([
                'status' => Response::HTTP_OK,
                'message' => __('settings.update')
            ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('settings.not_found'),
            'status' => Response::HTTP_NOT_FOUND

        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @return array
     */
    public function scope()
    {
        return [
            "namespace" => new NamespaceScope(),
            "area" => new AreaScope(),
            "search" => new SearchScope()
        ];
    }
}

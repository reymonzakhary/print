<?php

namespace App\Http\Controllers\Tenant\Mgr\Account\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateUserSettingRequest;
use App\Http\Resources\UserSettings\UserSettingResource;
use App\Models\Tenants\UserSetting;
use App\Scoping\Scopes\Settings\AreaScope;
use App\Scoping\Scopes\Settings\NameSpaceScope;
use App\Scoping\Scopes\Settings\SearchScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @group Tenant Account
 */
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
        $this->sortDir = $request->get('sort_dir') ?? 'desc';
    }

    /**
     * Account Settings
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     *     "data":[
     *      {
     *          "id": 1,
     *          "sort": 0,
     *          "name": "The default user theme colors",
     *          "key": "theme_colors",
     *          "secure_variable": false,
     *          "data_type": "objects",
     *          "data_variable": [],
     *          "multi_select": false,
     *          "incremental": null,
     *          "namespace": "themes",
     *          "area": "colors",
     *          "lexicon": null,
     *          "value": "#ffffff",
     *          "ctx": null
     *      }
     *     ]
     * }
     * 
     * @param Request $request
     * @return mixed
     */
    public function index(
        Request $request
    ): mixed
    {
        return UserSettingResource::collection(
            $request->user()->settings()->withScopes($this->scope())
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage)
        )->additional([
            'namespace' => UserSetting::groupBy('namespace')->pluck('namespace'),
            'area' => UserSetting::where('namespace', $request->get('namespace') ?? 'core')->groupBy('area')->pluck('area'),
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * @OA\Put(
     *   tags={"Account"},
     *   path="account/settings/{key}",
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
     *      description="update setting data",
     *      @OA\JsonContent(ref="#/components/schemas/UpdateUserSettingRequest"),
     *    ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/UserSettingResource")),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    /**
     * @param UpdateUserSettingRequest $request
     * @param UserSetting              $setting
     * @return UserSettingResource|JsonResponse
     */
    public function update(
        UpdateUserSettingRequest $request,
        UserSetting              $setting
    )
    {
        if ($setting->update($request->validated())) {

            return UserSettingResource::make(
                $setting->where('id', $setting->id)->first()
            )->additional([
                'status' => Response::HTTP_OK,
                'message' => null
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

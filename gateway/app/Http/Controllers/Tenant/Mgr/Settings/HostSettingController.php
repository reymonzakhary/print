<?php

namespace App\Http\Controllers\Tenant\Mgr\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateHostSettingRequest;
use App\Http\Requests\Settings\UpdateSupplierSettingRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HostSettingController extends Controller
{
    /**
     * @param UpdateSupplierSettingRequest $request
     * @param string                       $key
     * @return JsonResponse
     */
    public function __invoke(
        UpdateHostSettingRequest $request,
        string                   $key
    ): JsonResponse
    {
        $hostname = hostname();
        $settings = optional($hostname->configure)['settings'];
        $index = collect($settings)->search(fn($setting) => $setting['key'] === $key);
        $settings[$index] = array_merge(
            collect(optional($hostname->configure)['settings'])->filter(fn($setting) => $setting['key'] === $key)->values()->first(),
            ['value' => $request->value]
        );
        $hostname->configure['settings'] = $settings;
        if ($hostname->save()) {
            return response()->json([
                'message' => __('Setting has been updated successfully.'),
                'status' => Response::HTTP_OK

            ], Response::HTTP_OK);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('We could\'nt update the settings!'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }
}

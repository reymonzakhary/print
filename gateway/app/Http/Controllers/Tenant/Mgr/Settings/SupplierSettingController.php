<?php

namespace App\Http\Controllers\Tenant\Mgr\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSupplierSettingRequest;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SupplierSettingController extends Controller
{

    /**
     * @param UpdateSupplierSettingRequest $request
     * @param string                       $key
     * @return JsonResponse
     */
    public function __invoke(
        UpdateSupplierSettingRequest $request,
        string                       $key
    ): JsonResponse
    {
        $website = Website::where('uuid', tenant());
        $settings = optional($website->configure)['settings'];
        $index = collect($settings)->search(fn($setting) => $setting['key'] === $key);
        $settings[$index] = array_merge(
            collect(optional($website->configure)['settings'])->filter(fn($setting) => $setting['key'] === $key)->values()->first(),
            ['value' => $request->value]
        );
        $website->configure['settings'] = $settings;
        if ($website->save()) {
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

<?php

namespace App\Http\Controllers\Tenant\Mgr\Auth;

use App\Http\Controllers\Controller;
use App\Models\Impersonation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImpersonateController extends Controller
{
    /**
     * Updates the Impersonation data, creates a new session token and returns a JSON response.
     *
     * @param Request $request The incoming request instance
     * @return JsonResponse
     */
    public function __invoke(
        Request $request,
    )
    {
        Impersonation::where('target_tenant_id', tenant()->uuid)->delete();
        $custom_fields = \tenantCustomFields();
        $email = $custom_fields->pick('email');
        $company_name = $custom_fields->pick('company_name');
        $impersonation = Impersonation::create([
            'id' => Str::uuid(),
            'initiator_id' => 1,
            'target_tenant_id' => tenant()->uuid,
            'email' => $email,
            'supplier_name' => $company_name,
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            'expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'token' => $impersonation->id,
            'message' => 'Session token generated',
        ]);
    }
}

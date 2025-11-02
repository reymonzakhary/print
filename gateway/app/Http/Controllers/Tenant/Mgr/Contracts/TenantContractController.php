<?php

namespace App\Http\Controllers\Tenant\Mgr\Contracts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contracts\UpdateTenantContractRequest;
use App\Http\Resources\Settings\ContractResource;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantContractController extends Controller
{
    /**
     * Show method to retrieve and display a contract.
     *
     * @param Contract $contract The contract to be displayed
     * @return ContractResource|JsonResponse
     */
    public function show(
        Contract $contract,
    ): JsonResponse|ContractResource
    {
        if(!$contract->hasHandshake) {
            return response()->json([
                'message' => __('Contract not found!'),
                'status' => Response::HTTP_NOT_FOUND
            ],
            Response::HTTP_NOT_FOUND
            );
        }

        return ContractResource::make($contract)
            ->additional([
                'message' => __('Contract retrieved successfully!'),
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Update method to modify the details of a contract based on the provided request.
     *
     * @param Contract $contract The contract to be updated
     * @param UpdateTenantContractRequest $request The request containing updated data
     * @return JsonResponse The JSON response indicating the success or failure of the update
     */
    public function update(
        Contract $contract,
        UpdateTenantContractRequest $request,
    )
    {
        if(!$contract->hasHandshake) {
            return response()->json([
                'message' => __('Contract not found!'),
                'status' => Response::HTTP_NOT_FOUND
            ],
                Response::HTTP_NOT_FOUND
            );
        }
        $customFields = $contract->custom_fields ?? [];
        $customFields['contract'] = $request->validated('custom_fields');
        $contract->update([
            'custom_fields' => $customFields,
            'start_at' => $request->validated('start_at'),
            'end_at' => $request->validated('end_at'),
        ]);

        return response()->json([
            'message' => __('Contract updated successfully!'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }
}

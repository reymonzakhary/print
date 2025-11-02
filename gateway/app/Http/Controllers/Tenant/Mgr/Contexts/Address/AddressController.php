<?php

namespace App\Http\Controllers\Tenant\Mgr\Contexts\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreContextAddressRequest;
use App\Http\Requests\Addresses\UpdateContextAddressRequest;
use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Address\AddressResourceCollection;
use App\Models\Tenants\Address;
use App\Models\Tenants\Context;
use App\Repositories\AddressRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

use App\Services\Tenant\Categories\SupplierCategoryService;


final class AddressController extends Controller
{
    /**
     * default hiding field from response
     */
    private array $hide = [''];


    /**
     * @param Context $context
     *
     * @return AddressResourceCollection
     */
    public function index(
        Context $context
    ): AddressResourceCollection
    {
        return AddressResource::collection(
            $context->addresses()->with('country')->paginate(10)
        )
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * @param StoreContextAddressRequest $request
     * @param Context $context
     *
     * @return AddressResource
     */
    public function store(
        Context                    $context,
        StoreContextAddressRequest $request,
    ): AddressResource
    {
        return AddressResource::make(
            $request->address
        )
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => _('Address has been created successfully.')
            ]);
    }

    /**
     * @param Context $context
     * @param Address $address
     * @param UpdateContextAddressRequest $request
     *
     * @return AddressResource|JsonResponse
     */
    public function update(
        Context $context,
        Address $address,
        UpdateContextAddressRequest $request
    ): JsonResponse|AddressResource
    {
        return AddressResource::make($request->address)
            ->additional([
                'message' => __('Address has been updated successfully.'),
                'status' => Response::HTTP_OK,
            ]);
    }

    /**
     * @param Context $context
     * @param Address $address
     *
     * @return JsonResponse
     */
    public function destroy(
        Context $context,
        Address $address
    ): JsonResponse
    {
        $shareable_count = optional(
            app(SupplierCategoryService::class)
                ->obtainSharedCategoriesCount()
            )['sharable_count'];

        if ($context->addresses()->count() === 1 && $shareable_count) {
            throw ValidationException::withMessages([
                'data' => [
                    'message' => __("You have categories shared to the marketplace so at least one address's required")
                ]
                ]);
        }
        if (
            $context->addresses()->detach($address->id)
        ) {
            /**
             * error response
             */
            \App\Facades\Context::refresh();
            return response()->json([
                'data' => [
                    'message' => __('addresses.address_removed')
                ]
            ], Response::HTTP_ACCEPTED);
        }

        /**
         * error response
         */
        return response()->json([
            'data' => [
                'message' => __('addresses.bad_request')
            ]
        ], Response::HTTP_NOT_FOUND);
    }
}

<?php

namespace App\Http\Controllers\Tenant\Mgr\Users\Companies\Addresses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreAddressRequest;
use App\Http\Requests\Addresses\UpdateAddressRequest;
use App\Http\Resources\Address\AddressResource;
use App\Models\Tenant\Address;
use App\Models\Tenant\Company;
use App\Models\Tenant\User;
use App\Repositories\AddressRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    /**
     * default hiding field from response
     */
    private array $hide = [''];

    /**
     * @var AddressRepository
     */
    protected AddressRepository $address;

    /**
     * @var User
     */
    protected User $user;

    /**
     * AddressController constructor.
     * @param Address $address
     */
    public function __construct(
        Address $address
    )
    {
        $this->address = new AddressRepository($address);
    }

    /**
     * @param User    $user
     * @param Company $company
     * @return LengthAwarePaginator
     */
    public function index(
        User    $user,
        Company $company
    )
    {
        if ($user->companies()->where('companies.id', $company->id)->exists()) {
            return AddressResource::collection(
                $company->addresses()->paginate(10)
            )
                ->hide($this->hide)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }
    }

    /**
     * @param StoreAddressRequest $request
     * @param User                $user
     * @param Company             $company
     * @return AddressResource|JsonResponse
     */
    public function store(
        StoreAddressRequest $request,
        User                $user,
        Company             $company
    )
    {
        $address = $this->address->firstOrCreate($request->validated());

        $addressable = collect($request->validated())->filter(function ($v, $k) {
            return in_array($k, ['type', 'full_name', 'company_name', 'phone_number', 'tax_nr'], true);
        })->toArray();

        if ($user->companies()->where('companies.id', $company->id)->exists()) {
            $company
                ->addresses()
                ->syncWithoutDetaching([
                    $address->id => $addressable
                ]);

            return AddressResource::make($company
                ->addresses()->where('addresses.id', $address->id)->first())->hide($this->hide)->additional([
                'status' => Response:: HTTP_CREATED,
                'message' => null
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'message' => __('addresses.company_not_found'),
                'status' => Response::HTTP_NOT_FOUND
            ]
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param User                 $user
     * @param Company              $company
     * @param Address              $address
     * @param UpdateAddressRequest $request
     * @return AddressResource|JsonResponse
     */
    public function update(
        User                 $user,
        Company              $company,
        Address              $address,
        UpdateAddressRequest $request
    )
    {
        $search = collect($request->validated())->filter()->toArray();

        $results = $address->where(function ($q) use ($search) {
            return collect($search)->map(function ($keyword, $column) use ($q) {
                $q->where("{$column}", '=', "{$keyword}");
            });
        })->first();

        if ($results) {
            $company->addresses()->detach($address->id);
            $company
                ->addresses()
                ->syncWithoutDetaching([
                    $results->id => $request->only('type')
                ]);

            return AddressResource::make($results)
                ->hide($this->hide)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }

        if ($company->addresses()->detach($address->id)) {
            $new = $this->address->firstOrCreate($search);
            $company
                ->addresses()
                ->syncWithoutDetaching([
                    $new->id => $request->only('type')
                ]);

            return AddressResource::make($new)->hide($this->hide)->additional([
                'status' => Response:: HTTP_OK,
                'message' => null
            ]);
        }


        /**
         * error response
         */
        return response()->json([
            'data' => [
                'message' => __('addresses.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param User    $user
     * @param Company $company
     * @param Address $address
     * @return JsonResponse
     */
    public function destroy(
        User    $user,
        Company $company,
        Address $address
    )
    {
        if (
            $company->addresses()->detach($address->id)
        ) {
            /**
             * error response
             */
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

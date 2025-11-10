<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Account\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateTenantHostnameRequest;
use App\Http\Resources\Hostnames\HostnameResource;
use App\Models\Tenants\Address;
use App\Models\Tenants\Setting;
use App\Models\Tenants\User;
use App\Repositories\AddressRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Repositories\HostnameRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class TenantHostnameController extends Controller
{
    public function __construct(
        private readonly Environment $environment,
        private readonly AuthManager $authManager,
    ) {
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureUserIsAuthorized(): void
    {
        if (!$this->authManager->user()->isOwner()) {
            throw new AuthorizationException(
                message: __('Only owner of the tenant can access this resource.'),
            );
        }
    }

    /**
     * Show current tenant hostname data
     *
     * @return DomainResource
     *
     * @throws AuthorizationException
     */
    public function show(): HostnameResource
    {
        $this->ensureUserIsAuthorized();

        return HostnameResource::make(domain())
            ->hide([
                'id',
                'host_id',
                'tenant_id',
                'deleted_at',
            ]);
    }

    /**
     * Update current tenant hostname data
     *
     * @param UpdateTenantHostnameRequest $request
     * @param DomainRepository $hostnameRepository
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function update(
        UpdateTenantHostnameRequest $request,
        HostnameRepository $hostnameRepository,
    ): JsonResponse
    {
        $this->ensureUserIsAuthorized();

        $currentHostname = domain();

        if ($request->filled('logo')) {
            $currentHostname->setAttribute('logo', $request->get('logo'));
        }

        $currentHostname->setAttribute('custom_fields',
            array_merge(
                $currentHostname->getAttribute('custom_fields')->all()->toArray(),
                $request->safe([
                    'name',
                    'email',
                    'gender',
                    'tax_nr',
                    'company_name',
                    'coc',
                    'logo',
                    'page_title',
                    'page_description',
                    'page_media',
                    'shared_categories',
                    'manager_language',
                ])
            )
        );

        # We have to update through the repository to update the cache also
        $hostnameRepository->update($currentHostname);
        $currentHostname->operationCountries()
            ->sync(
                $request->get('operation_countries', [])
            );

        switchTenant($currentHostname->website()->first()->uuid);

        $user = User::query()->owner()->first();

        $user->update([
            'email' => $request->get('email'),
        ]);

        $company = $user->company()->updateOrCreate(
            [
                'id' => $user->company->id ?? null
            ],
            [
                "name" => $request->get('company_name'),
                "description" => $request->get('company_description'),
                "coc" => $request->get('company_coc'),
                "tax_nr" => $request->get('tax_nr'),
                "url" => $request->get('url'),
                'email' => $request->get('email'),
                'dial_code' => $request->get('dial_code'),
                'phone' => $request->get('phone'),
            ]
        );

        Setting::firstWhere('key' , 'currency')
            ?->update([
                'value' => $request->currency
            ]);



        if ($request->exists('manager_language')) {
            Setting::firstWhere('key', '=', 'manager_language')
                ?->update([
                    'value' => $request->get('manager_language')
                ]);
        }

        $addressRepository = app(AddressRepository::class);
        $address = $company?->addresses->first();
        if (!$address) {
            $address =  $addressRepository->firstOrCreate($request->validated('address'));
        }else{
            $addressRepository->update($address->id , $request->validated('address'));
        }
        $addressRepository->syncWithoutDetachingToModel(
            $address,
            $company,
            $request->validated('address')
        );



        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => __('Tenant settings has been updated successfully.')
        ]);
    }
}

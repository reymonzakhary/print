<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\V2\Mgr\Tenant;

use App\Enums\ContractType;
use App\Enums\MessageType;
use App\Enums\Status;
use App\Events\System\ClientUpdatedEvent;
use App\Foundation\ContractManager\Facades\ContractManager;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\ClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use App\Http\Resources\Hostnames\HostnameResource;
use App\Models\Area;
use App\Models\Contract;
use App\Models\DeliveryZone;
use App\Models\Domain;
use App\Models\Message;
use App\Models\Module;
use App\Models\Npace;
use App\Models\Quotation;
use App\Models\Tenants\Context;
use App\Models\Tenants\Role;
use App\Models\Tenants\Setting;
use App\Models\Tenants\Team;
use App\Models\Tenants\User;
use App\Models\Website;
use App\Providers\TenantAuthServiceProvider;
use App\Repositories\AddressRepository;
use App\Scoping\Scopes\Hostnames\FilterExternalFromHostnameScope;
use App\Scoping\Scopes\Hostnames\FilterSupplierFromHostnameScope;
use App\Scoping\Scopes\Hostnames\SearchHostnameScope;
use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class TenantController extends Controller
{
    public function __construct(
        private readonly FileSystemManager $fileSystemManager,
        private readonly DatabaseManager   $databaseManager,
    ) {
    }

    /**
     * Returns a list of all available tenants
     *
     * @return AnonymousResourceCollection|mixed
     */
    public function index(): mixed
    {
        return HostnameResource::collection(
            Domain::with('website', 'requestedContracts', 'receivedContracts')->withScopes($this->scope())->paginate(10)
        )
            ->additional([
                'message' => __('Tenants has been retrieved successfully'),
                'status' => Response::HTTP_OK,
            ]);
    }

    /**
     * Show a specific tenant
     *
     * @param Domain $tenant
     *
     * @return DomainResource
     */
    public function show(
        Domain $tenant,
    ): HostnameResource
    {
        return HostnameResource::make(
            $tenant->load(['website', 'operationCountries' => function ($query) {
                $query->select(['countries.id', 'countries.name']);
            }, 'requestedContracts', 'receivedContracts'])
        )
            ->additional([
                'message' => __('Tenant has retrieved successfully'),
                'status' => Response::HTTP_OK,
            ]);
    }

    /**
     * Create a new tenant (WITHOUT delivery zones)
     *
     * @param ClientRequest $request
     * @param WebsiteRepository $websiteRepository
     * @param DomainRepository $hostnameRepository
     * @param Environment $environment
     * @param Carbon $carbon
     *
     * @return DomainResource
     *
     * @throws Throwable
     */
    public function store(
        ClientRequest      $request,
        WebsiteRepository  $websiteRepository,
        HostnameRepository $hostnameRepository,
        Environment        $environment,
        Carbon             $carbon,
    ): HostnameResource
    {
        try {

            // Create website and tenant
            $website = new Website([
                'supplier' => $request->boolean('supplier')
            ]);

            $tenant = new Domain([
                'fqdn' => $request->string('fqdn')
            ]);

            $websiteRepository->create($website);
            $hostnameRepository->attach($tenant, $website);

            // Handle contract for suppliers (System contract - Type 1)
            if ($request->boolean('supplier')) {
                $contract = ContractManager::createSupplierContract(
                    $tenant->id,
                    $website->uuid,
                    $request->contract ?? [],
                    $request->input('can_request_quotation') ?? false,
                    [
                        'active' => true,
                        'st' => Status::ACCEPTED,
                        'activated_at' => Carbon::now()->toDateTimeString(),
                    ],
                );
                Message::query()->create([
                    'title' => 'Hi Prindustry,',
                    'subject' => 'producer request',
                    'body' => 'We would like to become a producer on your platform to showcase our offerings in the marketplace. Can you help me create a producer account?',
                    'parent_id' => null,
                    'to' => 'cec',
                    'type' => MessageType::PRODUCER->value,
                    'contract_id' => $contract->id,
                    'sender_hostname' => $tenant->id,
                    'sender_name' => Str::replace('  ', ' ', $request->get('first_name') . ' ' . $request->get('middle_name') . ' ' . $request->get('last_name')),
                    'sender_email' => $request->get('email'),
                    'from' => 'sender',
                    'recipient_hostname' => null,
                    'sender_user_id' => auth()->id(),
                    'recipient_user_id' => 1,
                ]);

                $tenant->operationCountries()
                    ->attach(
                        $request->get('operation_countries', [])
                    );

                if (!empty($request->external_configure)) {
                    $tenant->website->update([
                        'external' => 'true',
                        'configure' => $request->external_configure
                    ]);
                }

            }

            // NOTE: Delivery zones are NOT processed here anymore
            // They will be handled separately via storeDeliveryZones method

            $tenant->update([
                'logo' => $request->exists('logo') ?
                    $this->saveTenantLogoToStorage($request->file('logo'), $tenant)
                    : null,

                'configure' => $request->get('namespaces') ?
                    ['namespaces' => [$request->get('namespaces')]]
                    : $this->prepareConfiguration(),

                'custom_fields' => [
                    'ready' => false,
                    'dial_code' => $request->get('dial_code'),
                    'phone' => $request->get('phone'),
                    'email' => $request->get('email'),
                    'gender' => $request->get('gender'),
                    'name' => Str::replace('  ', ' ', $request->get('first_name') . ' ' . $request->get('middle_name') . ' ' . $request->get('last_name')),
                    'coc' => $request->get('company_coc'),
                    'company_name' => $request->get('company_name'),
                    'domain' => $request->get('domain'),
                    'password' => $request->get('password'),
                    'tax_nr' => $request->get('tax_nr'),
                    'email_send' => false,
                    'manager_language' => $request->get('manager_language', 'en'),
                ],

                'primary' => true
            ]);

            $environment->hostname($tenant);

            config(['auth.guards.api.provider' => 'tenant']);
            define('STDIN', fopen("php://stdin", "r"));
            app()->register(TenantAuthServiceProvider::class);

            Artisan::call('passport:client --password');
            Artisan::call('modules:sys:update');
            Artisan::call('modules:tenancy:migrate');

            Artisan::call(
                sprintf('tenancy:migrate --website_id=%s --path=Modules/Cms/Database/Migrations', $website->getAttribute('id'))
            );

            $environment->tenant($website);

            $user = User::create([
                'email' => $request->get('email'),
                'password' => $request->get('password'),
                'email_verified_at' => $carbon->now()->toDateTimeString(),
            ]);

            $user->profile()->create([
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'gender' => $request->get('gender'),
            ]);

            $company = $user->company()->create([
                "name" => $request->get('company_name'),
                "description" => $request->get('company_description'),
                "coc" => $request->get('company_coc'),
                "tax_nr" => $request->get('tax_nr'),
                "url" => $request->get('url'),
                'email' => $request->get('email'),
                'dial_code' => $request->get('dial_code'),
                'phone' => $request->get('phone'),
            ]);



            $addressRepository = app(AddressRepository::class);
            $address = $addressRepository->firstOrCreate($request->validated());
            $addressRepository->syncWithoutDetachingToModel(
                $address,
                $company,
                $request->validated()
            );

            $role = $request->get('role') ?
                Role::firstwhere('name', $request->get('role')) :
                Role::firstWhere('name', 'superadministrator');

            $team = Team::create([
                'name' => 'administrator',
                'display_name' => 'Administrator'
            ]);

            Team::create([
                'name' => 'member',
                'display_name' => 'Member'
            ]);

            $user->contexts()->attach(
                Context::findOrFail(1)
            );

            $user->addRole($role);
            $user->addRole($role, $team);

            $tenant->modules()->sync(
                Module::pluck('id')->toArray()
            );

            Artisan::call(
                sprintf(
                    'tenancy:db:seed --website_id=%s --class=\\Database\\\Seeders\\\Tenants\\\UserSettingSeeder',
                    $website->getAttribute('id')
                )
            );

            Artisan::call('modules:sys:update');

            Artisan::call(
                sprintf('modules:tenancy:migrate "" %s', $website->getAttribute('id'))
            );

            Cache::forget('WebsocketsServiceProvider_apps');

            $tenant->update([
                'custom_fields' => $tenant->getAttribute('custom_fields')->add('ready', true)
            ]);

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


            Log::info("Tenant created successfully", [
                'tenant_id' => $tenant->id,
                'fqdn' => $tenant->fqdn,
                'supplier' => $request->boolean('supplier'),
            ]);

            return HostnameResource::make(
                $tenant->load('website')
            )->additional([
                'message' => __('Tenant has been created successfully'),
                'status' => Response::HTTP_CREATED,
                'delivery_zones_endpoint' => route('tenants-create', $tenant->id),
            ]);

        } catch (\Exception $e) {
            Log::error("Tenant creation failed", [
                'request_data' => $request->except(['password', 'password_confirmation']),
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Update an existing tenant
     *
     * @param UpdateClientRequest $request
     * @param Domain $tenant
     *
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function update(
        UpdateClientRequest $request,
        Hostname            $tenant,
    ): JsonResponse
    {
        $this->databaseManager->transaction(
            function () use ($request, $tenant): void {
                $tenant->update([
                    'custom_fields' => [
                        'ready' => $tenant->custom_fields->pick('ready'),
                        'dial_code' => $request->get('dial_code'),
                        'phone' => $request->get('phone'),
                        'email' => $request->get('email'),
                        'gender' => $request->get('gender'),
                        'name' => Str::replace('  ', ' ', $request->get('first_name') . ' ' . $request->get('middle_name') . ' ' . $request->get('last_name')),
                        'coc' => $request->get('company_coc'),
                        'company_name' => $request->get('company_name'),
                        'domain' => $request->get('domain'),
                        'tax_nr' => $request->get('tax_nr'),
                        'email_send' => $tenant->custom_fields->pick('email_send'),
                        'manager_language' => $request->get('manager_language', 'en'),
                    ],
                    ]);

                if ($request->exists('logo')) {
                    $tenant->update([
                        'logo' => $this->saveTenantLogoToStorage($request->file('logo'), $tenant)
                    ]);
                }

                if (! $tenant->configure){
                    $tenant->update([
                        'configure' => $request->get('namespaces') ?
                            ['namespaces' => [$request->get('namespaces')]]
                            : $this->prepareConfiguration(),
                    ]);

                }

                $tenant->website()->first()
                    ?->forceFill([
                        'supplier' => $request->boolean('supplier')
                    ])
                    ->save();


                if ($request->boolean('supplier')) {
                    // Update or create system contract (Type 1) using ContractManager
                    // Changing The Structure For System Contracts , and reflect it for old data

                    $contract = ContractManager::updateOrMigrateSupplierContract(
                            $tenant->id , $tenant->website->uuid ,
                 $request->contract ?? [] , $request->input('can_request_quotation') ?? false ,
                        [
                            'active' => true,
                            'st' => Status::ACCEPTED,
                            'activated_at' => Carbon::now()->toDateTimeString(),
                        ],

                    );
                    $message = Message::query()->where([
                        'to' => 'cec',
                        'type' => MessageType::PRODUCER->value,
                        'sender_hostname' => $tenant->id,
                        'recipient_hostname' => null,
                        'from' => 'sender',
                        'contract_id' => $contract->id,
                    ])->exists();

                    if (!$message){
                        Message::query()->create([
                            'title' => 'Hi Prindustry,',
                            'subject' => 'producer request',
                            'body' => 'We would like to become a producer on your platform to showcase our offerings in the marketplace. Can you help me create a producer account?',
                            'parent_id' => null,
                            'to' => 'cec',
                            'type' => MessageType::PRODUCER->value,
                            'contract_id' => $contract->id,
                            'sender_hostname' => $tenant->id,
                            'sender_name' => Str::replace('  ', ' ', $request->get('first_name') . ' ' . $request->get('middle_name') . ' ' . $request->get('last_name')),
                            'sender_email' => $request->get('email'),
                            'from' => 'sender',
                            'recipient_hostname' => null,
                            'sender_user_id' => auth()->id(),
                            'recipient_user_id' => 1,
                        ]);

                    }


                    $tenant->operationCountries()
                        ->sync(
                            $request->get('operation_countries', [])
                        );

                    if (!empty($request->external_configure)) {
                        $tenant->website->update([
                            'external' => 'true',
                            'configure' => $request->external_configure
                        ]);
                    }
                }

                switchTenant($tenant->website()->first()->uuid);

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
                    $address =  $addressRepository->firstOrCreate($request->validated());
                }else{
                    $addressRepository->update($address->id , $request->validated());
                }
                $addressRepository->syncWithoutDetachingToModel(
                    $address,
                    $company,
                    $request->validated()
                );



            }
        );

        ClientUpdatedEvent::dispatch($tenant);

        return response()->json([
            'message' => __('Tenant has been updated successfully'),
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Delete a specific tenant
     *
     * @param Domain $tenant
     *
     * @return JsonResponse
     *
     * @throws Exception
     * @throws Throwable
     */
    public function destroy(
        Domain $tenant,
    ): JsonResponse
    {
        $website = $tenant->website()->firstOrFail();
        /* @var Website $website */

        $this->databaseManager->transaction(
            function () use ($tenant, $website): void {
                // Delete all tenant contracts and their dependencies
                $this->deleteTenantContracts($tenant);

                $tenant->forceDelete();

                foreach (['tenants', 'assets', 'carts'] as $baseFolderName) {
                    $this->fileSystemManager->disk('tenancy')->deleteDirectory(
                        sprintf('%s/%s', $baseFolderName, $website->getAttribute('uuid'))
                    ) ?: throw new RuntimeException(
                        sprintf('Could not delete directory "%s" while trying to destroy tenant "%s"',
                            $tenant->getAttribute('fqdn'),
                            $baseFolderName
                        )
                    );
                }
            }
        );

        $this->databaseManager->getDoctrineSchemaManager()->dropDatabase(
            sprintf('"%s"', $website->getAttribute('uuid'))
        );

        return response()->json([
            'message' => __('Tenant has been deleted successfully'),
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @return array
     */
    private function prepareConfiguration(): array
    {
        return [
            'namespaces' => Npace::with('areas')->get()
                ->flatMap(
                    static fn(Npace $namespace): Collection => collect($namespace->areas()->get())
                        ->unique()
                        ->map(
                            static fn(Area $area): array => [
                                'area' => $area->getAttribute('slug'),
                                'namespace' => $namespace->getAttribute('slug'),
                            ]
                        )
                )
                ->toArray()
        ];
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param Domain $tenant
     *
     * @return string
     *
     * @throws Throwable
     */
    private function saveTenantLogoToStorage(
        UploadedFile $uploadedFile,
        Hostname     $tenant,
    ): string
    {
        return $this->fileSystemManager->disk('digitalocean')
            ->putFileAs(
                'suppliers',
                $uploadedFile,
                sprintf('%s.%s',
                    $tenant->website()->firstOrFail()->getAttribute('uuid'),
                    $uploadedFile->extension()
                )
            ) ?:
            throw new RuntimeException(
                'Could not save the logo file on the storage disk'
            );
    }

    public function scope(): array
    {
        return [
            'search' => new SearchHostnameScope(),
            'supplier' => new FilterSupplierFromHostnameScope(),
            'external' => new FilterExternalFromHostnameScope(),
        ];
    }

    /**
     * Delete all contracts associated with a tenant and their dependencies
     *
     * @param Domain $tenant
     * @return void
     * @throws Exception
     */
    private function deleteTenantContracts(Domain $tenant): void
    {
        $tenantContracts = ContractManager::getMyContracts($tenant);

        foreach ($tenantContracts as $contract) {
            // Delete messages associated with this contract
            Message::where('contract_id', $contract->id)->delete();
            // Delete the contract
            $contract->delete();
            Log::info("Deleted contract during tenant deletion", [
                'tenant_id' => $tenant->id,
                'contract_id' => $contract->id,
            ]);
        }
        Log::info("All tenant contracts deleted", [
            'tenant_id' => $tenant->id,
            'contracts_deleted' => $tenantContracts->count(),
        ]);
    }
}

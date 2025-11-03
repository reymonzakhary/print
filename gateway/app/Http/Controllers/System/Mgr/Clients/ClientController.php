<?php

namespace App\Http\Controllers\System\Mgr\Clients;

use App\Events\System\ClientUpdatedEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clients\ClientRequest;
use App\Http\Requests\Clients\UpdateClientRequest;
use App\Http\Resources\Hostnames\HostnameResource;
use App\Models\Domain;
use App\Models\Module;
use App\Models\Npace;
use App\Models\Supplier;
use App\Models\Tenants\Context;
use App\Models\Tenants\Role;
use App\Models\Tenants\Setting;
use App\Models\Tenants\Team;
use App\Models\Tenants\User;
use App\Providers\TenantAuthServiceProvider;
use Carbon\Carbon;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Website;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Throwable;

class ClientController extends Controller
{

    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * @return \Inertia\Response
     */
    public function page()
    {
        return Inertia::render('Tenants', []);
    }

    /**
     * @return LengthAwarePaginator
     */
    public function index()
    {
        return HostnameResource::collection(Domain::with('website')->paginate(10));
    }

    /**
     * @param
     * @return DomainResource|JsonResponse
     */
    public function show(string $client)
    {
        $hostname = Domain::find($client);
        if ($hostname) {
            return HostnameResource::make($hostname);
        }

        return response()->json([
            'message' => __('Client not found.'),
            'status' => HttpFoundationResponse::HTTP_NOT_FOUND
        ], HttpFoundationResponse::HTTP_NOT_FOUND);
    }

    /**
     * @param ClientRequest $request
     * @return ResponseFactory|Response|ResponseFactory
     */
    public function store(
        ClientRequest $request
    )
    {
        if (!Domain::where('fqdn', $request->get('fqdn'))->exists()) {
            /**
             * create website
             */
            $website = new Website;

            app(WebsiteRepository::class)->create($website);

            /**
             * if tenant is supplier set website to supplier
             */
            $website->supplier = (bool) $request->supplier;
            $website->save();

            /**
             * link website with hostname
             */
            $hostname = new Hostname;
            $hostname->fqdn = $request->fqdn;


            app(HostnameRepository::class)->attach($hostname, $website);

            /** add user to supplier list */
            $supplier = Supplier::create([
                'supplier_id' => $website->uuid,
                'name' => $request->fqdn
            ]);

            $file = $request->file('logo');
            $hostname->configure = $request->get('namespaces') ? ['namespaces' => [$request->get('namespaces')]] : $this->prepareConfiguration();

            $hostname->custom_fields = [
                'ready' => false,
                'email' => $request->email,
                'gender' => $request->gender,
                'name' => Str::replace('  ', ' ', $request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name),
                'coc' => $request->company_coc,
                'company_name' => $request->company_name,
                'domain' => $request->domain,
                'password' => $request->password,
                'tax_nr' => $request->tax_nr,
                'email_send' => false
            ];

            /**
             * set hostname to be primary as we don't have subtenants in the system
            */
            $hostname->primary = true;

            if ($file) {
                $storage = Storage::disk('digitalocean')->putFileAs(
                    'suppliers',
                    $file,
                    $website->uuid . '.' . $file->extension()
                );
                if ($storage) {
                    $hostname->logo = $storage;

                }
            }
            $hostname->update();
            /**
             * switch environment
             */
            app(Environment::class)->hostname($hostname);

            config(['auth.guards.api.provider' => 'tenant']);
            define('STDIN', fopen("php://stdin", "r"));
            app()->register(TenantAuthServiceProvider::class);
            Artisan::call('passport:client --password');

            // wait until seeder is done
            sleep(15);

            Artisan::call('modules:sys:update');
            Artisan::call('modules:tenancy:migrate');
            Artisan::call('tenancy:migrate --website_id=' . $website->id . ' --path=Modules/Cms/Database/Migrations');
            app(Environment::class)->tenant($website);
            $user = User::create([
                'email' => $request->email,
                'password' => $request->password,
                'email_verified_at' => Carbon::now()->timestamp
            ]);

            $user->profile()->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender
            ]);

            $role = $request->get('role') ?
                Role::where('name', $request->get('role'))->first()
                :
                Role::where('name', 'superadministrator')->first();

            $team = Team::create(
                [
                    'name' => 'administrator',
                    'display_name' => 'Administrator'
                ]
            );

            Team::create(
                [
                    'name' => 'member',
                    'display_name' => 'Member'
                ]
            );
            $user->companies()->create([
                "name" => $request->company_name,
                "description" => $request->company_description,
                "coc" => $request->company_coc,
            ]);

            $context = Context::find(1);

            $user->addRole($role);
            $user->contexts()->attach($context);
            $user->addRole($role, $team);


            $hostname->modules()->sync(Module::pluck('id')->toArray());

            Artisan::call('tenancy:db:seed --class=\\Database\\\Seeders\\\Tenants\\\UserSettingSeeder');
            Artisan::call('modules:sys:update');
            Artisan::call('modules:tenancy:migrate');

            Cache::forget('WebsocketsServiceProvider_apps');

            $hostname->custom_fields->add('ready', true);
            $hostname->update();

            if($request->manager_language) {
                Setting::updated([
                    'manager_language' => $request->manager_language
                ]);
            }


            return response([
                'message' => __('auth.client_created'),
                'status' => 201
            ], 201);
        }
        return response([
            'message' => __('auth.client_exists'),
            'status' => 422
        ], 422);
    }

    /**
     * @param UpdateClientRequest $request
     * @param mixed $client
     * @return Response|ResponseFactory
     */
    public function update(
        UpdateClientRequest $request,
        $client
    )
    {

        $hostname = Domain::where('id', $client)->first();

        if ($hostname) {
            $hostname->configure = $request->get('namespaces') ? ['namespaces' => $request->get('namespaces')] : $this->prepareConfiguration();

            $hostname->custom_fields = array_merge($hostname->custom_fields->toArray(), [
                'gender' => $request->gender,
                'name' => Str::replace('  ', ' ', $request->first_name . ' ' . $request->middle_name . ' ' . $request->last_name),
                'tax_nr' => $request->tax_nr,
                'company_name' => $request->company_name,
                'coc' => $request->company_coc,
                'domain' => $request->domain,
            ]);

            $hostname->save();

            $website = $hostname->website()->first();
            $website->supplier = (bool) $request->supplier;
            $website->save();

            ClientUpdatedEvent::dispatch($hostname);

            return response([
                'message' => __('Client data updated successfully'),
                'status' => HttpFoundationResponse::HTTP_OK
            ], HttpFoundationResponse::HTTP_OK);
        }

        return response([
            'message' => __('auth.client_not_found'),
            'status' => HttpFoundationResponse::HTTP_NOT_FOUND
        ], HttpFoundationResponse::HTTP_NOT_FOUND);
    }

    /**
     * @param int $id
     * @return Response|ResponseFactory
     */
    public function destroy(
        int $id
    ): Response|ResponseFactory
    {
        $hostname = Domain::where('id', $id)->first();

        if ($website = $hostname?->website()->first()) {

            try {

                Storage::disk('tenancy')->deleteDirectory('tenants/'. $website->uuid . '/');
                Storage::disk('tenancy')->deleteDirectory('assets/'. $website->uuid . '/');
                Storage::disk('tenancy')->deleteDirectory('carts/'. $website->uuid . '/');

                DB::statement('DROP DATABASE IF EXISTS "' . $website->uuid . '"');
                $hostname->contracts()->forceDelete();
                $website->forceDelete();
                $hostname->forceDelete();

                return response([
                    'message' => __('Client deleted.'),
                    'status' => HttpFoundationResponse::HTTP_OK
                ], HttpFoundationResponse::HTTP_OK);

            } catch (Throwable $th) {

                return response([
                    'message' => $th->getMessage(),
                    'status' => HttpFoundationResponse::HTTP_BAD_REQUEST
                ], HttpFoundationResponse::HTTP_BAD_REQUEST);
            }
        }

        return response([
            'message' => __('auth.client_not_found'),
            'status' => HttpFoundationResponse::HTTP_NOT_FOUND
        ], HttpFoundationResponse::HTTP_NOT_FOUND);
    }

    /**
     * @return array
     */
    #[ArrayShape(['namespaces' => "array|mixed[]"])] public function prepareConfiguration(): array
    {
        return [
            'namespaces' => Npace::with('areas')->get()->flatMap(fn($n) => collect($n->areas)->unique()->map(fn($a) => [
                'area' => $a->slug,
                'namespace' => $n->slug,
            ]))->toArray()
        ];
    }
}

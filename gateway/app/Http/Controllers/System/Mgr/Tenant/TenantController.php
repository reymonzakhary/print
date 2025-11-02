<?php

namespace App\Http\Controllers\System\Mgr\Tenant;

use App\Http\Controllers\Controller;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Http\Request;

/**
 * Class TenantController
 * @package App\Http\Controllers\System\Mgr\Tenant
 */
class TenantController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $request->merge(['fqdn' => $request->name . '.' . env('TENANT_URL_BASE')]);

        if (!Hostname::where('fqdn', $request->get('fqdn'))->exists()) {
            /**
             * create website
             */
            $website = new Website;
            app(WebsiteRepository::class)->create($website);

            /**
             * link website with hostname
             */
            $hostname = new Hostname;
            $hostname->fqdn = $request->get('fqdn');
            app(HostnameRepository::class)->attach($hostname, $website);

            /**
             * switch environment
             */
            app(Environment::class)->hostname($hostname);

            config(['auth.guards.api.provider' => 'tenant']);

//            Artisan::call('passport:client --password');
        }

        return 'done';
    }
}

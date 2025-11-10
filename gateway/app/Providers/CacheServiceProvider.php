<?php

namespace App\Providers;

use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class CacheServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if(!$this->app->runningInConsole()){
            if (PHP_SAPI === 'fpm-fcgi') {
                $tenantId = Str::slug(env('APP_NAME', 'laravel'), '_').'_cache';
            } else {
                // Use stancl/tenancy helper to get current tenant
                $currentTenant = tenant();

                if ($currentTenant) {
                    $tenantId = $currentTenant->id;
                } else {
                    // Fallback: try to find tenant by domain
                    $fqdn = request()->getHost();
                    $tenantId = DB::connection('cec')
                        ->table('domains')
                        ->where('domain', $fqdn)
                        ->value('tenant_id') ?? Str::slug(env('APP_NAME', 'laravel'), '_').'_cache';
                }
            }
            Cache::store('redis')->setPrefix(Str::slug($tenantId, '_').'_cache');
        }

    }
}

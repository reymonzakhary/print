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
                $uuid = Str::slug(env('APP_NAME', 'laravel'), '_').'_cache';
            } else {
                $fqdn = request()->getHost();
                $uuid = DB::table('hostnames')
                    ->select('websites.uuid')
                    ->join('websites', 'hostnames.website_id', '=', 'websites.id')
                    ->where('fqdn', $fqdn)
                    ->value('uuid');
            }
            Cache::store('redis')->setPrefix(Str::slug(tenant()?->uuid??$uuid, '_').'_cache');
        }

    }
}

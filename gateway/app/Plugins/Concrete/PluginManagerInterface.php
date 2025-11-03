<?php

declare(strict_types=1);

namespace App\Plugins\Concrete;

use App\Models\Domain;
use App\Plugins\Config\DefaultConfigRepository;
use Illuminate\Http\Request;

interface PluginManagerInterface
{
    /**
     * Handle the request to bus with the specified runner.
     *
     * @param Request $request
     * @param array $runner
     * @return void
     */
    public function bus(Request $request, array $runner = []): void;

    /**
     * Register a new tenant with the given information.
     *
     * @param string $uri The URI of the tenant
     * @param int $port The port of the tenant
     * @param array $routes The routes for the tenant
     * @param string $tenant_id The ID of the tenant
     * @param string $tenant_name The name of the tenant
     * @param \Hyn\Tenancy\Models\Hostname|Hostname $hostname The hostname for the tenant
     * @param DefaultConfigRepository $configRepository The repository for default configuration
     *
     * @return void
     */
    public function register(
        string $uri,
        int $port,
        array $routes,
        string $tenant_id,
        string $tenant_name,
        \Hyn\Tenancy\Models\Hostname|Hostname $hostname,
        DefaultConfigRepository $configRepository
    ): void;
}

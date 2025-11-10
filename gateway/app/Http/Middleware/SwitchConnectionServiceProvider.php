<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Foundation\Settings\Settings;
use App\Models\Tenant\Media;
use App\Providers\TenantAuthServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;


final class SwitchConnectionServiceProvider
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    final public function handle(Request $request, Closure $next): mixed
    {
        $request->domain = $request->getSchemeAndHttpHost();

        // Use stancl/tenancy helpers
        // These helpers only work because InitializeTenancyByDomain middleware ran first
        $currentTenant = tenant();
        $currentDomain = domain();

        // Debug logging (remove in production)
        if (config('app.debug')) {
            \Log::debug('SwitchConnectionServiceProvider Debug:', [
                'request_host' => $request->getHost(),
                'request_url' => $request->url(),
                'tenant_found' => $currentTenant ? $currentTenant->id : 'NULL',
                'domain_found' => $currentDomain ? $currentDomain->domain : 'NULL',
            ]);
        }

        if ($currentDomain && $fqdn = $currentDomain->domain) {
            config(['auth.guards.api.provider' => 'tenant']);
            config(['auth.guards.web.provider' => 'tenant']);
            config(['database.default' => 'tenant']);

            if (!Str::contains($fqdn, env('TENANT_URL_BASE'))) {
                config(['session.same_site' => 'none']);
            }

            config(['queue.connections.database' => 'tenant']);
            config(['media-library.media_model' => Media::class]);

            $request->tenant = $currentTenant;
            $request->uuid = $currentTenant?->id;
            $request->domain = $currentDomain;

            request()->merge([
                'tenant' => $request->tenant,
                'uuid' => $request->uuid,
                'domain' => $request->domain,
                'host_id' => $request->domain?->host_id
            ]);

            dd(Settings::mailSmtpHosts());
            app()->register(TenantAuthServiceProvider::class);


            config([
                'mail.mailers.smtp.host' => Settings::mailSmtpHosts(),
                'mail.mailers.smtp.port' => (int)Settings::mailSmtpPort(),
                'mail.mailers.smtp.username' => Settings::mailSmtpUser(),
                'mail.mailers.smtp.password' => Settings::mailSmtpPass(),
                'mail.mailers.smtp.encryption' => Settings::mailSmtpPrefix(),
                'mail.from.name' => Settings::mailSmtpFromName(),
                'mail.from.address' => Settings::mailSmtpFrom(),
            ]);

            config(['app.fallback_locale', Settings::managerLanguage()]);

            return $next($request);
        }

        /**
         * check if the manager then go on
         */
        $host = request()->headers->get('host');
        $subdomain = Str::before($host, '.');
        if ($subdomain === 'manager') {
            return $next($request);
        }

        /**
         * else abort found the tenant
         */
        abort(Response::HTTP_NOT_FOUND, __('Not found!'));

    }
}

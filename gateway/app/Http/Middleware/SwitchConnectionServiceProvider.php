<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Foundation\Settings\Settings;
use App\Models\Tenants\Media;
use App\Providers\TenantAuthServiceProvider;
use Closure;
use Hyn\Tenancy\Environment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;


final readonly class SwitchConnectionServiceProvider
{
    public function __construct(
        private Environment $environment,
    ) {
    }

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

        if ($fqdn = $this->environment->domain()?->fqdn) {
            config(['auth.guards.api.provider' => 'tenant']);
            config(['auth.guards.web.provider' => 'tenant']);
            config(['database.default' => 'tenant']);

            if (!Str::contains($fqdn, env('TENANT_URL_BASE'))) {
                config(['session.same_site' => 'none']);
            }

            config(['queue.connections.database' => 'tenant']);
            config(['media-library.media_model' => Media::class]);

            $request->tenant = $this->environment->tenant();
            $request->uuid = $this->environment->tenant()?->uuid;
            $request->hostname = domain();

            request()->merge([
                'tenant' => $request->tenant,
                'uuid' => $request->uuid,
                'hostname' => $request->hostname,
                'host_id' => $request->hostname?->host_id
            ]);

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

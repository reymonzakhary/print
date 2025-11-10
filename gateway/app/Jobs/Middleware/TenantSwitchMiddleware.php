<?php

namespace App\Jobs\Middleware;


use App\Facades\Settings;
use BeyondCode\LaravelWebSockets\Apps\App;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use LogicException;

class TenantSwitchMiddleware
{
    public function handle($command, $next)
    {
        Broadcast::purge('pusher');

        // Use stancl/tenancy helpers
        $currentTenant = tenant();
        $currentDomain = $currentTenant?->primary_domain ?? $currentTenant?->domains->first();
        $fqdn = $currentDomain?->domain;

        if (!$fqdn) {
            Log::error('command not found', ['command' => $command]);

            throw new LogicException(
                sprintf('command not found, "%s"', $command)
            );
        }

        // Only switch the broadcast's configuration when there is a connection details found for this tenant
        if ($app = App::findByKey($fqdn)) {
            config([
                'broadcasting.connections.pusher' => [
                    'driver' => 'pusher',
                    'key' => $app->key,
                    'secret' => $app->secret,
                    'app_id' => $app->id,
                    'options' => [
                        'cluster' => env('PUSHER_APP_CLUSTER'),
                        'encrypted' => true,
                        'host' => '127.0.0.1',
                        'port' => 6001,
                        'scheme' => 'http'
                    ],
                ]
            ]);
        }

        config([
            'mail.mailers.smtp.host' => Settings::mailSmtpHosts()->value,
            'mail.mailers.smtp.port' => (int)Settings::mailSmtpPort()->value,
            'mail.mailers.smtp.username' => Settings::mailSmtpUser()->value,
            'mail.mailers.smtp.password' => Settings::mailSmtpPass()->value,
            'mail.mailers.smtp.encryption' => Settings::mailSmtpPrefix()->value,
            'mail.from.name' => Settings::mailSmtpFromName()->value,
            'mail.from.address' => Settings::mailSmtpFrom()->value,
        ]);
        config(['app.fallback_locale', Settings::managerLanguage()->value]);
        return $next($command);
    }
}

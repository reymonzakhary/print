<?php

namespace App\Providers;

use BeyondCode\LaravelWebSockets\Apps\App;
use Hyn\Tenancy\Environment;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $request = request();
        $referer = $request->headers->get('referer');
        $host = $request->headers->get('host');
        if ($referer !== null && $referer !== $host) {
            $referer = str_replace(["http://", "https://", "ws://", "://", ":3000", ":6001"], "", $referer);
            $referer = (string)explode('/', $referer)[0];
            $request->headers->set('host', $referer);
            $request->headers->set('referer', $referer);
        }
        $request->domain = $request->getSchemeAndHttpHost();
        $env = app(Environment::class);

        if ($fqdn = optional($env->hostname())->fqdn) {
            $app = App::findByKey($fqdn);
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

//        if ($fqdn = optional(app(Environment::class)->hostname())->fqdn) {
//            $app = \BeyondCode\LaravelWebSockets\Apps\App::findByKey($fqdn);
//            config([
//                'broadcasting.connections.pusher' => [
//                    'driver' => 'pusher',
//                    'key' => $app->key,
//                    'secret' => $app->secret,
//                    'app_id' => $app->id,
//                    'options' => [
//                        'cluster' => env('PUSHER_APP_CLUSTER'),
//                        'encrypted' => true,
//                        'host' => '127.0.0.1',
//                        'port' => 6001,
//                        'scheme' => 'http'
//                    ],
//                ]
//            ]);
//        }

        Broadcast::routes(['prefix' => 'api/v1/mgr', "middleware" => ['auth:tenant', 'verified', 'auth.ctx:mgr']]);
        require base_path('routes/tenant/channels.php');
    }
}

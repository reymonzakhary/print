<?php

namespace App\Providers;

use App\Models\Tenant;
use BeyondCode\LaravelWebSockets\Apps\App;
use BeyondCode\LaravelWebSockets\Apps\AppProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class WebsocketsServiceProvider implements AppProvider
{
    /** @var Collection */
    protected $apps;

    public function __construct()
    {
        $this->apps = Cache::remember('WebsocketsServiceProvider_apps', 3600, function () {
            return Tenant::with('domains')->get()->flatMap(fn($tenant) =>
                collect($tenant?->domains)->map(fn($domain) => [
                        'id' => $tenant->id,
                        'name' => $tenant->id,
                        'key' => $domain?->domain,
                        'secret' => $tenant->id,
                        'path' => $tenant->id,
                        'capacity' => null,
                        'enable_client_messages' => false,
                        'enable_statistics' => true,
                    ]
                )
            );
        });

    }

    /**  @return array[\BeyondCode\LaravelWebSockets\AppProviders\App] */
    public function all(): array
    {
        return $this->apps->map(fn(array $appAttributes) => $this->instantiate($appAttributes))
            ->toArray();
    }

    /**
     * @param $appId
     * @return App|null
     */
    public function findById($appId): ?App
    {
        $appAttributes = $this
            ->apps
            ->firstWhere('id', $appId);

        return $this->instantiate($appAttributes);
    }

    public function findByKey(string $appKey): ?App
    {
        $appAttributes = $this
            ->apps
            ->firstWhere('key', $appKey);

        return $this->instantiate($appAttributes);
    }

    public function findBySecret(string $appSecret): ?App
    {
        $appAttributes = $this
            ->apps
            ->firstWhere('secret', $appSecret);

        return $this->instantiate($appAttributes);
    }

    protected function instantiate(?array $appAttributes): ?App
    {
        if (!$appAttributes) {
            return null;
        }

        $app = new App(
            $appAttributes['id'],
            $appAttributes['key'],
            $appAttributes['secret']
        );

        if (isset($appAttributes['name'])) {
            $app->setName($appAttributes['name']);
        }

        if (isset($appAttributes['host'])) {
            $app->setHost($appAttributes['host']);
        }

        if (isset($appAttributes['path'])) {
            $app->setPath($appAttributes['path']);
        }

        $app
            ->enableClientMessages($appAttributes['enable_client_messages'])
            ->enableStatistics($appAttributes['enable_statistics'])
            ->setCapacity($appAttributes['capacity'] ?? null);

        return $app;
    }
}

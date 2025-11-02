<?php

namespace App\Providers;

use App\Console\Commands\ManageWebhooksCommand;
use App\Plugins\Concrete\PluginFactoryInterface;
use App\Plugins\Config\DefaultConfigRepository;
use App\Plugins\Config\PluginConfigRepository;
use App\Plugins\Contracts\PluginFactory;
use App\Plugins\PluginService;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class PluginServiceProvider extends ServiceProvider
{
    // Defined class constants are also easier to change later
    const string PLUGIN_NAMESPACE = '\\App\\Plugins\\Src\\';
    const string PLUGIN_DIRECTORY = 'Plugins/Src';

    protected array $plugins = [];

    /**
     * Register the application dependencies.
     *
     * @return void
     * @throws Exception
     */
    public function register(): void
    {
        // Always register these critical bindings, even in console mode
        $this->app->bind(PluginConfigRepository::class, fn() => $this->bindDefaultConfigRepository());
        $this->app->singleton(PluginFactoryInterface::class, fn() => new PluginFactory());

        // Only load heavy plugin classes when not in console OR when it's a queue worker
        if (false === app()->runningInConsole() || $this->isQueueWorker()) {
            $this->getPluginClasses();

            // Bind plugin managers
            $this->app->bind('plugin.managers', fn() => $this->plugins);

            // Register the enhanced plugin service
            $this->app->singleton('plugins', fn() => new PluginService());
        }

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ManageWebhooksCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish migration if running in console
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../database/migrations/create_plugin_webhook_events_table.php' =>
                    database_path('migrations/' . date('Y_m_d_His', time()) . '_create_plugin_webhook_events_table.php'),
            ], 'plugin-migrations');
        }
    }

    /**
     * Binds the DefaultConfigRepository to the application.
     *
     * @return DefaultConfigRepository
     */
    protected function bindDefaultConfigRepository(): DefaultConfigRepository
    {
        return new DefaultConfigRepository();
    }

    /**
     * Retrieves the classes of plugins located in the plugin directory and registers them with the application.
     *
     * @return void
     * @throws Exception
     */
    protected function getPluginClasses(): void
    {
        $pluginPath = app_path(self::PLUGIN_DIRECTORY);

        if (!File::exists($pluginPath)) {
            return;
        }

        $plugins = array_filter(File::files($pluginPath), fn ($file) => $file->getExtension() === 'php');

        foreach ($plugins as $file) {
            $pluginClass = Str::before(self::PLUGIN_NAMESPACE . $file->getBasename(),'.php');
            if (class_exists($pluginClass)) {
                $this->registerPlugin(app::make($pluginClass), $pluginClass);
            }
        }
    }

    /**
     * Register a plugin with the application.
     *
     * @param mixed $plugin
     * @param string $className
     * @return void
     * @throws Exception
     */
    public function registerPlugin(
        mixed $plugin,
        string $className
    ): void
    {
        if ($plugin instanceof \App\Plugins\Concrete\PluginManagerInterface) {
            $this->plugins[$className] = $plugin;
        } else {
            throw new Exception("Invalid plugin: {$className} does not implement PluginManagerInterface");
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            'plugins',
            'plugin.managers',
            PluginConfigRepository::class,
            PluginFactoryInterface::class,
        ];
    }

    /**
     * Check if the application is currently executing a queue worker command.
     *
     * @return bool
     */
    private function isQueueWorker(): bool
    {
        if (!app()->runningInConsole()) {
            return false;
        }

        $argv = $_SERVER['argv'] ?? [];
        return in_array('queue:work', $argv) ||
            in_array('queue:listen', $argv) ||
            in_array('horizon', $argv);
    }
}

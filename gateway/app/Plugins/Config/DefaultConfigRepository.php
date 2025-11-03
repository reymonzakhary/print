<?php

namespace App\Plugins\Config;


use App\Models\Domain;
use Illuminate\Validation\ValidationException;

class DefaultConfigRepository implements PluginConfigRepository
{
    /**
     * @var mixed
     */
    public mixed $config;

    /**
     * Load instance of the website
     * @var \Hyn\Tenancy\Models\Hostname|Hostname $hostname
     */
    public \Hyn\Tenancy\Models\Hostname|Hostname $hostname;


    /**
     * @return string
     */
    public function getBaseUri():string
    {
        return $this->getRoutePrefix().":".$this->getPluginPort();
    }

    /**
     * Get the route prefix for the plugin.
     *
     * @return string
     */
    public function getRoutePrefix(): string
    {
        return $this->config->plugin_route_prefix;
    }

    /**
     * Get the port for the plugin.
     *
     * @return int
     */
    public function getPluginPort(): int
    {
        return $this->config->plugin_port;
    }

    /**
     * Get the name of the plugin.
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return $this->config->plugin_name;
    }

    /**
     * Get the plugin version from the config
     *
     * @return string
     */
    public function getPluginVersion(): string
    {
        return $this->config->plugin__v;
    }

    /**
     * Get the plugin routes from the application configuration.
     *
     * @return array
     */
    public function getPluginRoutes(): array
    {
        return $this->config->plugin_routes;
    }

    /**
     * Update the website for the application.
     *
     * @param \Hyn\Tenancy\Models\Hostname|Hostname|int|null $hostname The website model to update
     * @return self
     * @throws ValidationException
     */
    public function update(\Hyn\Tenancy\Models\Hostname|Hostname|int|null $hostname = null): self
    {

        $this->hostname = $this->resolveHostname($hostname);
        $this->config = $this->extractConfiguration();

        $this->boot();
        return $this;
    }

    /**
     * Resolve hostname from various input types
     *
     * @param int|\Hyn\Tenancy\Models\Hostname|Hostname|null $hostname
     * @return \Hyn\Tenancy\Models\Hostname|null
     * @throws ValidationException
     */
    private function resolveHostname(
        int|\Hyn\Tenancy\Models\Hostname|Hostname|null $hostname
    ): ?\Hyn\Tenancy\Models\Hostname
    {
        if ($hostname === null) {
            // Try to get current hostname from tenancy context
            try {
                $website = \Hyn\Tenancy\Facades\TenancyFacade::website();
                if ($website) {
                    return $website->domains()->first();
                }
            } catch (\Exception $e) {
                // Ignore exception, will be handled in boot()
            }
            return null;
        }

        if (is_numeric($hostname)) {
            $resolvedHostname = Domain::where('id', $hostname)
                ->with('website')
                ->first();

            if (!$resolvedHostname) {
                throw ValidationException::withMessages([
                    'hostname' => ["Hostname with ID {$hostname} not found."]
                ]);
            }

            return $resolvedHostname;
        }

        if (in_array(get_class($hostname), [Domain::class, '\Hyn\Tenancy\Models\Hostname'])) {
            // Ensure the website relationship is loaded
            if (!$hostname->relationLoaded('website')) {
                $hostname->load('website');
            }
            return $hostname;
        }

        throw ValidationException::withMessages([
            'hostname' => ['Invalid hostname type provided. Expected Hostname instance, ID, or null.']
        ]);
    }

    /**
     * Extract and validate configuration from hostname
     *
     * @return object|null
     * @throws ValidationException
     */
    private function extractConfiguration(): ?object
    {
        if (!$this->hostname || !$this->hostname->website) {
            return null;
        }

        $configure = $this->hostname->website->getAttribute('configure');

        // Handle different configuration storage formats
        if (is_string($configure)) {
            $decoded = json_decode($configure);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'plugin_config' => ['Invalid plugin configuration format: ' . json_last_error_msg()]
                ]);
            }
            return $decoded;
        }

        if (is_array($configure)) {
            return (object) $configure;
        }

        if (is_object($configure)) {
            return $configure;
        }

        return null;
    }

    /**
     * Boot the application.
     * Throws a ValidationException if the hostname's website is not set as external.
     * @throws ValidationException
     */
    protected function boot(): void
    {
        // Check if hostname exists
        if (!$this->hostname) {
            throw ValidationException::withMessages([
                'hostname' => [__('No hostname provided or found.')]
            ]);
        }

        // Check if website exists
        if (!$this->hostname->website) {
            throw ValidationException::withMessages([
                'website' => [__('No website associated with this hostname.')]
            ]);
        }

        $website = $this->hostname->website;

        // Check if tenant is eligible for plugins (supplier flag)
        if (!$website->getAttribute('supplier')) {
            throw ValidationException::withMessages([
                'supplier' => [__('This tenant is not configured as a supplier.')]
            ]);
        }

        // Check if tenant uses external services
        if (!$website->getAttribute('external')) {
            throw ValidationException::withMessages([
                'tenant' => [__('This function is not available for internal system users.')]
            ]);
        }

        // Check if plugin is configured
        if (!$this->config) {
            throw ValidationException::withMessages([
                'plugin' => [__('This plugin is not configured yet, please contact the system administrator.')]
            ]);
        }

        // Validate required plugin configuration fields
        $this->validatePluginConfig();
    }

    /**
     * Validate plugin configuration has required fields
     *
     * @throws ValidationException
     */
    private function validatePluginConfig(): void
    {
        $requiredFields = [
            'plugin_name' => 'Plugin name',
            'plugin_port' => 'Plugin port',
            'plugin_route_prefix' => 'Plugin route prefix'
        ];

        $missingFields = [];

        foreach ($requiredFields as $field => $label) {
            if (!isset($this->config->{$field}) || empty($this->config->{$field})) {
                $missingFields[] = $label;
            }
        }

        if (!empty($missingFields)) {
            throw ValidationException::withMessages([
                'plugin_config' => [
                    __('Missing required plugin configuration: :fields', [
                        'fields' => implode(', ', $missingFields)
                    ])
                ]
            ]);
        }

        // Validate plugin routes if they exist
        if (isset($this->config->plugin_routes) && !is_array($this->config->plugin_routes)) {
            throw ValidationException::withMessages([
                'plugin_routes' => [__('Plugin routes must be an array.')]
            ]);
        }
    }

    /**
     * Check if the current tenant supports webhooks
     *
     * @return bool
     */
    public function supportsWebhooks(): bool
    {
        return $this->hostname
            && $this->hostname->website
            && $this->hostname->website->supplier
            && $this->hostname->website->external
            && $this->config !== null;
    }

    /**
     * Get webhook configuration for this tenant
     *
     * @return array
     */
    public function getWebhookConfiguration(): array
    {
        if (!$this->supportsWebhooks()) {
            return [];
        }

        // Check if webhook settings are explicitly configured
        if (isset($this->config->webhook_settings) && is_array($this->config->webhook_settings)) {
            return $this->config->webhook_settings;
        }

        // Return default webhook configuration based on plugin type
        return $this->getDefaultWebhookConfiguration();
    }

    /**
     * Get default webhook configuration based on plugin
     *
     * @return array
     */
    private function getDefaultWebhookConfiguration(): array
    {
        // Return basic default configuration - all customization should come from database
        return [
            [
                'models' => ['App\\Models\\Tenants\\Order', 'App\\Models\\Tenants\\Quotation'],
                'events' => ['created', 'updated'],
                'webhook_endpoint' => 'webhook/sync',
                'queue_mode' => 'background',
                'queue_name' => 'plugin-webhooks',
                'delay' => 0,
            ]
        ];
    }
}

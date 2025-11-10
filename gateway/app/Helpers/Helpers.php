<?php

use App\Casts\Hostname\CustomFieldCast;
use App\Models\Domain;
use App\Models\Language;
use App\Models\Tenant;
use App\Models\Tenant\Setting;
use App\Models\Website;
use App\Plugins\Moneys;
use BeyondCode\LaravelWebSockets\Apps\App;
use Carbon\Carbon;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Cms\Entities\Eloquent\Collection;

/**
 * Helper function
 * Copyright @reymonZakhary
 * Prindustry B.v
 */

//if (! function_exists('gmp_sign')) {
//    function gmp_sign($model)
//    {
//        $plural = Str::plural(class_basename($model));
//
//        return Str::kebab($plural);
//    }
//}


if (!function_exists('parseMailSetting')) {
    /**
     * Convert a setting's "value" into a CSS-ready string.
     *
     * @param string|null $value  The setting value from DB
     * @return string
     */
    function parseMailSetting(?string $value): string
    {
        if (is_null($value) || trim($value) === '') {
            return '';
        }

        // Handle "objects" style e.g. "pt:32px,pb:32px,pl:16px,pr:16px"
        if (strpos($value, ':') !== false && strpos($value, ',') !== false) {
            $parts = [];
            foreach (explode(',', $value) as $item) {
                [$key, $val] = array_map('trim', explode(':', $item, 2));
                // Map shorthand
                $cssProp = match (strtolower($key)) {
                    'pt' => 'padding-top',
                    'pb' => 'padding-bottom',
                    'pl' => 'padding-left',
                    'pr' => 'padding-right',
                    'tl' => 'border-top-left-radius',
                    'tr' => 'border-top-right-radius',
                    'br' => 'border-bottom-left-radius',
                    'bl' => 'border-bottom-right-radius',
                    default => $key,
                };
                if (!str_contains($val, "px")) {
                    $val .= "px";
                }
                $parts[] = "$cssProp: $val";
            }
            return implode('; ', $parts);
        }


        // For plain values (like '16px' or '#fff') just return
        return $value;
    }
}


if (!function_exists('moneys')) {
    /**
     * Create a new Moneys instance
     *
     * @param float|int|string|null $amount Optional initial amount
     * @param string|null $currency Optional currency code
     * @return Moneys
     *
     * @example
     * ```php
     * // Basic usage
     * echo moneys()->setAmount(1500)->format();
     *
     * // With VAT
     * echo moneys()
     *     ->setTax(15)
     *     ->setAmount(1000)
     *     ->format(inc: true);
     *
     * // Quick factory
     * echo moneys(1500, 'EUR')->format();
     * ```
     */
    function moneys(float|int|string|null $amount = null, ?string $currency = null): Moneys
    {
        $instance = new Moneys();

        if ($amount !== null) {
            $instance->setAmount($amount);
        }

        if ($currency !== null) {
            $instance->setCurrency($currency);
        }

        return $instance;
    }
}

if (!function_exists('ean13Generator')) {
    function ean13Generator(): string
    {
        $time = Carbon::now()->timestamp . random_int(0, 9);
        $code = '2' . str_pad($time, 10, '0', STR_PAD_LEFT);
        $weightily = true;
        $sum = 0;
        // Weight for a digit in the checksum is 3, 1, 3.. starting from the last digit.
        // loop backwards to make the loop length-agnostic. The same basic functionality
        // will work for codes of different lengths.
        for ($i = strlen($code) - 1; $i >= 0; $i--) {
            $sum += (int)$code[$i] * ($weightily ? 3 : 1);
            $weightily = !$weightily;
        }
        $code .= (10 - ($sum % 10)) % 10;
        return $code;
    }
}

if (!function_exists('domain')) {
    /**
     * Get the current domain using tenant-based resolution for multi-tenancy.
     * This approach is compatible with Laravel Reverb and ensures proper tenant isolation.
     *
     * @return Domain|null
     */
    function domain(): ?Domain
    {
        try {
            // Use tenant-based resolution via the tenant relationship
            // This is more reliable for multi-tenancy and Reverb compatibility
            $tenant = tenant();

            if ($tenant) {
                // Get the domain from the tenant relationship (domain-based)
                return $tenant->primary_domain ?? $tenant->domains->first();
            }

            // Fallback to package default (CurrentDomain contract)
            return Domain::current();
        } catch (\Throwable $th) {
            //\Log::debug(['domain' =>$th->getMessage(), 'helper' => 'domain helper']);
            return null;
        }
    }
}

if (!function_exists('hostname')) {
    /**
     * Legacy helper for backward compatibility
     * @deprecated Use domain() instead
     *
     * @return Domain|null
     */
    function hostname(): ?Domain
    {
        return domain();
    }
}

if (!function_exists('tenantCustomFields')) {
    function tenantCustomFields(): ?CustomFieldCast
    {
        $currentTenant = tenant();
        $domain = $currentTenant?->primary_domain ?? $currentTenant?->domains->first();
        return $domain?->custom_fields;
    }
}

if (!function_exists('tenantLogoUrl')) {
    function tenantLogoUrl(): mixed
    {
        $currentTenant = tenant();
        $domain = $currentTenant?->primary_domain ?? $currentTenant?->domains->first();
        $logoPath = $domain?->getAttribute('logo');

        return $logoPath ? Storage::disk('digitalocean')->url($logoPath) : null;
    }
}

if (!function_exists('website')) {
    function website()
    {
        $currentTenant = tenant();
        return $currentTenant ? Website::where('id', $currentTenant->id)->first() : null;
    }
}

// Note: tenant() helper is now provided by stancl/tenancy package
// It returns the current tenant instance or null

if (!function_exists('tenants')) {
    function tenants()
    {
        return Domain::all();
    }
}

if (!function_exists('supplierName')) {
    function supplierName($id)
    {
        $tenant = Tenant::find($id);
        return $tenant?->primary_domain?->domain ?? $tenant?->domains->first()?->domain;
    }
}

if(!function_exists('modules_path')) {
    function modules_path($model, $path) {
        return str_replace("//", "/", dirname(app()->path(), 1). "/Modules/{$model}/$path");
    }
}

if (!function_exists('shortCode')) {
    function shortCode($string, $data = [])
    {
        $string = str_replace(['%', '.', '[[', ']]'], ['$', '->', '{', '}'], $string);
        eval("\$string = \"$string\";");
        return $string;
    }
}
if (!function_exists('cleanName')) {

    function cleanName($string)
    {
        $string = preg_replace('#/+#', '/', $string);
        return str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    }
}

if (!function_exists('getDisplayName')) {
    function getDisplayName($displayName, $lang = null)
    {
        $lang = (empty($lang)) ? app()->getLocale() : $lang;
        // is string
        if (is_string($displayName))
            return $displayName;
        //is array
        if (is_array($displayName)) {
            $display_name = collect($displayName)->filter(fn($itm) => optional($itm)['iso'] === $lang)->values()->pluck('display_name')->first() ?? null;
            return (!empty($display_name)) ? $display_name : $displayName[0]['display_name'];
        }
    }
}

if (!function_exists('setDisplayName')) {
    function setDisplayName($name, $connection = 'tenant')
    {
        $lang = \App\Models\Tenant\Language::get();
        if ($connection === 'system') {
            $lang = Language::get();
        }
        return $lang->map(fn($i) => [
            'name' => $name,
            'iso' => $i->iso,
            'display_name' => $name
        ])->toArray();
    }
}


if (!function_exists('random_password')) {
    function random_password(
        int $length
    ): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' .
            '0123456789';
        $special_chars = '@$!%*#?&';

        $str = '';
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, $max)];
        }
        for ($i = 0; $i < 3; $i++) {
            $str = substr_replace(
                $str,
                $special_chars[rand(0, strlen($special_chars) - 1)],
                rand(0, strlen($str) - 1),
                0
            );
        }
        return $str;

    }
}

if (!function_exists('switchSupplier')) {
    function switchSupplier($uuid)
    {
        $tenant = Tenant::find($uuid);
        if ($tenant) {
            tenancy()->initialize($tenant);
            DB::disconnect();
            DB::reconnect();
        }
    }
}


if (!function_exists('switchTenant')) {
    function switchTenant($uuid): void
    {
        $tenant = Tenant::find($uuid);

        if (!$tenant) {
            throw new \Exception("Tenant with ID {$uuid} not found.");
        }

        // Switch the tenant using stancl/tenancy
        tenancy()->initialize($tenant);

        // Reset tenant-related services
        rebootTenantContainer($tenant);

        // Purge & reconnect the database
        DB::purge('tenant');
        DB::reconnect('tenant');

        // 🔥 Force middleware to reload
        reloadTenantRequest($tenant);
    }
}

if (!function_exists('switchToSystem')) {
    function switchToSystem(): void
    {
        // End current tenancy
        tenancy()->end();

        // Purge tenant connection
        DB::purge('tenant');

        // Get the central connection name from config
        $centralConnection = config('tenancy.database.central_connection', 'cec');

        // Set back to central/system connection
        config(['database.default' => $centralConnection]);

        // Reconnect to system
        DB::reconnect($centralConnection);

        // Clear request headers that might affect routing
        request()->headers->set('referer', 'manager' . env('APP_URL'));
        request()->headers->set('host', 'manager' . env('APP_URL'));
    }
}

if (!function_exists('rebootTenantContainer')) {
    function rebootTenantContainer($tenant): void
    {
        $domain = $tenant->primary_domain?->domain ?? $tenant->domains->first()?->domain;
        if ($domain) {
            request()->headers->set('referer', $domain);
            request()->headers->set('host', $domain);
        }
        // Ensure correct database connection is loaded for the tenant
        DB::purge('tenant');
    }
}

if (!function_exists('reloadMiddlewareInstance')) {
    function reloadTenantRequest($site): void
    {
        $old_request =  request()->all();
        // Explicitly clear the request instance to ensure fresh data
        app()->instance('request', new \Illuminate\Http\Request());

        // Get the primary domain for the tenant
        $domain = $site->primary_domain ?? $site->domains->first();

        // Update the request with the new tenant data
        request()->replace(array_merge($old_request, [
            'tenant' => $site,
            'uuid' => $site->id,
            'domain' => $domain,
            'host_id' => $domain?->host_id
        ]));

        // 🔥 Rebind the SupplierCategoryService to ensure it gets the updated request data
        app()->bind(\App\Services\Tenant\Categories\SupplierCategoryService::class, function ($app) {
            return new \App\Services\Tenant\Categories\SupplierCategoryService();
        });

    }
}

if (!function_exists('switchSupplierWebsocket')) {
    /**
     * Switch websocket connection for a specific tenant using domain-based resolution.
     * This ensures proper multi-tenant isolation and is compatible with both
     * Laravel WebSockets and Laravel Reverb.
     *
     * @param string $uuid The tenant's UUID
     * @return void
     */
    function switchSupplierWebsocket(string $uuid): void
    {
        // Use domain as the key for websocket configuration
        // This ensures proper tenant isolation in multi-tenant environments
        $tenant = Tenant::find($uuid);

        if (!$tenant) {
            return;
        }

        $fqdn = ($tenant->primary_domain ?? $tenant->domains->first())?->domain;

        // Find websocket app by domain (fqdn) - not by hostname
        if (!$websocketConfig = App::findByKey($fqdn)) {
            return;
        }

        Broadcast::purge();

        config([
            'broadcasting.connections.pusher' => [
                'driver' => 'pusher',
                'key' => $websocketConfig->key,
                'secret' => $websocketConfig->secret,
                'app_id' => $websocketConfig->id,
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
}

if (!function_exists('cloneData')) {

    /**
     * Summary of cloneData
     * @param mixed $local_disk
     * @param mixed $local_path
     * @param mixed $dist_disk
     * @param mixed $dist_path
     * @return void
     */
    function cloneData($local_disk, $local_path, $dist_disk, $dist_path = null)
    {
        $dist_path = $dist_path ? $dist_path : $local_path;
        Storage::disk($dist_disk)->put(
            $dist_path,
            Storage::disk($local_disk)->get($local_path)
        );
    }
}

if (!function_exists('getObject')) {
    /**
     * walk throught object
     * @param mixed $object
     * @param array $variables
     * @return mixed
     */
    function walk($object, array $variables)
    {
        $newObject = $object;
        while (!empty($variables)) {
            $key = array_shift($variables);

            if (in_array($key, ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'ctx_id', 'ctx', 'created_from', 'reference'])) {
                $newObject = null;
                break;
            }

            if (is_array($newObject) || $newObject instanceof Collection || $newObject instanceof ArrayObject){
                $newObject = optional($newObject)[$key];
            } else {
                $newObject = optional($newObject)->{$key};
            }
        }

        return $newObject;
    }
}

/**
 *
 */
if (!function_exists('sanitizeToInt')) {
    /**
     * Sanitize input value to an integer
     *
     * @param mixed $value The input value to sanitize
     * @return int The sanitized integer value
     */
    function sanitizeToInt($value): int
    {
        // Extract only numbers from the input using a regular expression
        $number = preg_replace('/\D/', '', $value);

        // If the result is empty, return 0; otherwise, cast to int
        return $number === '' ? 0 : (int)$number;
    }
}

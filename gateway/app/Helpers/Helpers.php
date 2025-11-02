<?php

use App\Casts\Hostname\CustomFieldCast;
use App\Models\Hostname;
use App\Models\Language;
use App\Models\Tenants\Setting;
use App\Models\Website;
use App\Plugins\Moneys;
use BeyondCode\LaravelWebSockets\Apps\App;
use Carbon\Carbon;
use Hyn\Tenancy\Environment;
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

if (!function_exists('hostname')) {
    function hostname()
    {
        try {
            return Hostname::current();
        } catch (\Throwable $th) {
            //\Log::debug(['hostname' =>$th->getMessage(), 'helper' => 'Line 54 in helper']);
            return null;
        }
    }
}

if (!function_exists('tenantCustomFields')) {
    function tenantCustomFields(): CustomFieldCast
    {
        return tenant()->hostnames->first()->custom_fields;
    }
}

if (!function_exists('tenantLogoUrl')) {
    function tenantLogoUrl(): mixed
    {
        $logoPath = tenant()->hostnames()->first()?->getAttribute('logo');

        return $logoPath ? Storage::disk('digitalocean')->url($logoPath) : null;
    }
}

if (!function_exists('website')) {
    function website()
    {
        return Website::where('uuid', tenant()->uuid)->first();
    }
}

if (!function_exists('tenant')) {
    function tenant()
    {
        return app(Environment::class)->tenant();
    }
}

if (!function_exists('tenants')) {
    function tenants()
    {
        return Hostname::all();
    }
}

if (!function_exists('supplierName')) {
    function supplierName($uuid)
    {
        return Website::where('uuid', $uuid)->with('hostnames')->first()->hostnames->first()->fqdn;
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
        $lang = \App\Models\Tenants\Language::get();
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
        $env = app(Environment::class);
        $site = Website::where('uuid', $uuid)->first();
        $env->tenant($site);
        DB::disconnect();
        DB::reconnect();
    }
}


if (!function_exists('switchTenant')) {
    function switchTenant($uuid): void
    {
        $env = app(\Hyn\Tenancy\Environment::class);
        $site = \App\Models\Website::where('uuid', $uuid)->with('hostname')->first();

        if (!$site) {
            throw new \Exception("Tenant with UUID {$uuid} not found.");
        }

        // Switch the tenant
        $env->tenant($site);

        // Reset tenant-related services
        rebootTenantContainer($site);

        // Ensure the tenant's DB connection is correctly set
        app(\Hyn\Tenancy\Database\Connection::class)->set($site);

        // Purge & reconnect the database
        DB::purge('tenant');
        DB::reconnect('tenant');

        // 🔥 Force middleware to reload
        reloadTenantRequest($site);
    }
}

if (!function_exists('switchToSystem')) {
    function switchToSystem(): void
    {
        $env = app(\Hyn\Tenancy\Environment::class);

        // Clear the current tenant
        $env->tenant(null);

        // Purge tenant connection
        DB::purge('tenant');

        // Set back to system connection
        config(['database.default' => 'system']);

        // Reconnect to system
        DB::reconnect('system');

        // Clear cached instances
        app()->forgetInstance(\Hyn\Tenancy\Database\Connection::class);

        // Clear request headers that might affect routing
        request()->headers->set('referer', 'manager' . env('APP_URL'));
        request()->headers->set('referer', 'manager' . env('APP_URL'));
    }
}

if (!function_exists('rebootTenantContainer')) {
    function rebootTenantContainer($site): void
    {
        request()->headers->set('referer', $site->hostname->fqdn);
        request()->headers->set('host', $site->hostname->fqdn);
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

        // Update the request with the new tenant data
        request()->replace(array_merge($old_request, [
            'tenant' => $site,
            'uuid' => $site->uuid,
            'hostname' => $site->hostname,
            'host_id' => $site->hostname?->host_id
        ]));

        // 🔥 Rebind the SupplierCategoryService to ensure it gets the updated request data
        app()->bind(\App\Services\Tenant\Categories\SupplierCategoryService::class, function ($app) {
            return new \App\Services\Tenant\Categories\SupplierCategoryService();
        });

    }
}

if (!function_exists('switchSupplierWebsocket')) {
    function switchSupplierWebsocket(string $uuid): void
    {
        $fqdn = Website::query()
            ->where('uuid', $uuid)
            ->firstOrFail()
            ->hostnames()
            ->firstOrFail()
            ->getAttribute('fqdn');

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

<?php

namespace App\Facades;

use App\Models\Domain;
use App\Plugins\PluginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * @author Reymon Zakhary
 * Represents a facade for the Plugins class.
 * @method static PluginService load(int|\Illuminate\Database\Eloquent\Model|null|\Hyn\Tenancy\Models\Hostname|Domain $hostname = null)
 * @method static static handel(string|array ...$args)
 * @method static static bus(Request $request, array $array)
 * @method static static getSyncPipelineConfig()
 * @method static static auth(array $array)
 *
 * @see \App\Plugins\PluginService;
 */
class Plugins extends Facade
{
    /**
     * Get the accessor for the Facade.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'plugins';
    }
}

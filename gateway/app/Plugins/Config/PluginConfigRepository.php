<?php

namespace App\Plugins\Config;


use App\Models\Domain;

/**
 * @see DefaultConfigRepository;
 */
interface PluginConfigRepository
{

    /**
     * Get the plugin base uri with port
     * @return string
     */
    public function getBaseUri():string;

    /**
     * Get the prefix for routes in the application
     *
     * @return string
     */
    public function getRoutePrefix():string;

    /**
     * Get the port number for the plugin
     *
     * @return int
     */
    public function getPluginPort():int;

    /**
     * Get the name of the plugin.
     *
     * @return string
     */
    public function getPluginName():string;

    /**
     * Get the version of the plugin.
     *
     * @return string
     */
    public function getPluginVersion():string;

    /**
     * Get the routes defined by the plugins.
     *
     * @return array
     */
    public function getPluginRoutes():array;

    /**
     * Update the specified website.
     *
     * @param \Hyn\Tenancy\Models\Hostname|int|Hostname|null $hostname The website to be updated.
     * @return self
     */
    public function update( \Hyn\Tenancy\Models\Hostname|int|Hostname|null $hostname = null):self;

}

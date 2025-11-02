<?php

namespace Modules\Cms\Core\Cache;

use Illuminate\Cache\CacheManager;

class Cacheable
{
    protected string $driver;

    protected string $fallback_driver = 'file';

    protected $cache;

    /**
     * @param CacheManager $manager
     */
    public function __construct(
        public CacheManager $manager
    ) {
        $this->driver = env('CACHE_DRIVER');
        $this->boot();
    }

    /**
     *
     */
    public function boot()
    {
        try {
            $this->cache =  $this->manager->driver($this->driver);
        } catch (\Exception $e) {
            \Log::debug($e->getMessage());
            $this->cache = $this->manager->driver($this->fallback_driver);
        }
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get(
        $key
    )
    {
        return $this->cache->get($key);
    }


    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function remember(
        $key,
        $value
    ): mixed
    {
        $key = tenant()->uuid. $key;
        return $this->cache->remember($key, $value);
    }

}

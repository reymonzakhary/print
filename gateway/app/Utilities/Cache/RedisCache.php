<?php

namespace App\Utilities\Cache;

use Illuminate\Support\Facades\Cache;

class RedisCache
{
    /**
     * Store a key-value pair in Redis with a time-to-live (TTL) in minutes.
     *
     * @param string $key The cache key.
     * @param string $value The value to be stored.
     * @param int $ttlMinutes Time to live in minutes (default: 60).
     * @return void
     */
    public static function put(string $key, string $value, int $ttlMinutes = 60): void
    {
        Cache::store('redis')->put($key, $value, now()->addMinutes($ttlMinutes));
    }

    /**
     * Store a key-value pair in Redis permanently (no expiration).
     *
     * @param string $key The cache key.
     * @param string $value The value to be stored.
     * @return void
     */
    public static function forever(string $key, string $value): void
    {
        Cache::store('redis')->forever($key, $value);
    }

    /**
     * Retrieve a value from Redis by key.
     *
     * @param string $key The cache key.
     * @param mixed|null $default The default value to return if the key does not exist.
     * @return mixed The cached value or the default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::store('redis')->get($key, $default);
    }

    /**
     * Check if a specific key exists in Redis.
     *
     * @param string $key The cache key.
     * @return bool True if the key exists, false otherwise.
     */
    public static function has(string $key): bool
    {
        return Cache::store('redis')->has($key);
    }

    /**
     * Remove a key and its value from Redis.
     *
     * @param string $key The cache key to delete.
     * @return void
     */
    public static function forget(string $key): void
    {
        Cache::store('redis')->forget($key);
    }
}

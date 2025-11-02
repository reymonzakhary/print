<?php


namespace App\Generates;

/**
 * Class Identifier
 * @package App\Generates
 */
class Identifier
{
    /**
     * @param int $id
     * @param     $model
     * @return string
     */
    final public function generate(int $id, string $model): string
    {

        $key = md5($id . env('SYSTEM_PUBLIC_KEY'));

        if ($this->inCache($key)) {
            return $this->fromCache($key);
        }

        return $this->addToCache(
            $key,
            hash('sha256', $model . $id . env('SYSTEM_SECRET_KEY'))
        );

    }

    /**
     * @param string $key
     * @param string $value
     * @return mixed
     */
    final public function addToCache(
        string $key,
        string $value
    )
    {
        $this->cache[$key] = $value;
        return $value;
    }

    /**
     * @param $key
     * @return bool
     */
    final public function inCache(
        string $key
    ): bool
    {
        return isset($this->cache[$key]);
    }

    /**
     * @param $key
     * @return mixed
     */
    final public function fromCache(
        string $key
    )
    {
        return $this->cache[$key];
    }
}

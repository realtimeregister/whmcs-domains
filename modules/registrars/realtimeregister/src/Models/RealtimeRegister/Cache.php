<?php

namespace RealtimeRegisterDomains\Models\RealtimeRegister;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class Cache
{
    public const TABLE_NAME = 'mod_realtimeregister_cache_v2';
    protected $table = self::TABLE_NAME;

    private static ?AdapterInterface $pool = null;

    private static function pool(): AdapterInterface
    {
        if (self::$pool === null) {
            if (defined('PHPUNIT_REALTIMEREGISTER_TESTSUITE')) {
                self::$pool = new ArrayAdapter();
            } else {
                self::$pool = new PdoAdapter(
                    Capsule::connection()->getPdo(),
                    'realtimeregister',
                    3600,
                    ['db_table' => self::TABLE_NAME]
                );
            }
        }
        return self::$pool;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param mixed $default
     * @return mixed
     * @throws InvalidArgumentException
     */
    public static function get(string $key, $default = null)
    {
        $item = self::pool()->getItem(self::fixKey($key));

        return $item->isHit() ? $item->get() : $default;
    }


    /**
     * Some characters are not allowed..
     */
    private static function fixKey(string $key): string
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '|', $key);
    }

    /**
     * Store an item in the cache.
     *
     * @param  mixed         $value
     * @return void
     */
    public static function put(string $key, $value, int $minutes)
    {
        $item = self::pool()->getItem(self::fixKey($key));
        $item->set($value);
        $item->expiresAfter($minutes * 60);
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @return mixed
     */
    public static function remember(string $key, int $minutes, callable $callback)
    {
        return self::pool()->get(self::fixKey($key), function (ItemInterface $item) use ($minutes, $callback) {
            $item->expiresAfter($minutes * 60);
            return $callback();
        });
    }

    /**
     * *poof* Forget *poof*
     */
    public static function forget(string $key): bool
    {
        return self::pool()->deleteItem(self::fixKey($key));
    }

    /**
     * Remember forever
     */
    public static function rememberForever(string $key, callable $callback)
    {
        return self::pool()->get(self::fixKey($key), function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(null); // never expire automatically
            return $callback();
        });
    }
}

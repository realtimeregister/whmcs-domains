<?php

namespace RealtimeRegisterDomains\Models\RealtimeRegister;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\DatabaseStore;
use Illuminate\Cache\Repository;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Schema\Blueprint;

class Cache
{
    public const TABLE_NAME = 'mod_realtimeregister_cache';
    protected $table = self::TABLE_NAME;

    protected static Repository | CacheRepository | null $cache;

    public static function request(): CacheRepository
    {
        if (defined('PHPUNIT_REALTIMEREGISTER_TESTSUITE')) {
            // Use in-memory array cache — no database required
            self::$cache = new Repository(new ArrayStore());
        } else {
            // Use database-backed cache (same as original)
            if (!defined('PHPUNIT_REALTIMEREGISTER_TESTSUITE') && !Capsule::schema()->hasTable(Cache::TABLE_NAME)) {
                Capsule::schema()->create(
                    Cache::TABLE_NAME,
                    function (Blueprint $table) {
                        $table->string('key')->unique();
                        $table->mediumText('value');
                        $table->integer('expiration');
                    }
                );
            }
            $store = new DatabaseStore(Capsule::connection(), self::TABLE_NAME);
            self::$cache = new Repository($store);
        }
        return self::$cache;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $value = self::request()->get($key);
        if ($value !== null) {
            $decoded = json_decode($value, true);
            if ($decoded !== null) {
                return $decoded;
            }

            if (is_null($value)) {
                $value = value($default);
            }
        }

        return $value;
    }

    /**
     * Store an item in the cache.
     *
     * @param  mixed         $value
     * @return void
     */
    public static function put(string $key, $value, int $minutes)
    {
        self::request()->put($key, json_encode($value), ($minutes * 60));
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @return mixed
     */
    public static function remember(string $key, int $minutes, \Closure $callback)
    {
        $value = self::get($key);

        // If the item exists in the cache we will just return this immediately and if
        // not we will execute the given Closure and cache the result of that for a
        // given number of minutes so it's available for all subsequent requests.
        if (!is_null($value)) {
            return $value;
        }

        self::put($key, $value = $callback(), $minutes);

        return $value;
    }

    /**
     * *poof* Forget *poof*
     */
    public static function forget(string $key): void
    {
        self::request()->forget($key);
    }

    /**
     * Remember forever
     */
    public static function rememberForever(string $key, $value): void
    {
        self::request()->forever($key, $value);
    }
}

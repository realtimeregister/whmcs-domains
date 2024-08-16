<?php

namespace RealtimeRegister\Models\RealtimeRegister;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\DatabaseStore;
use Illuminate\Cache\Repository;
use Illuminate\Database\Capsule\Manager as Capsule;
use RealtimeRegister\Models\Whmcs\Registrars;

class Cache
{
    public const TABLE_NAME = 'mod_realtimeregister_cache';
    protected string $table = self::TABLE_NAME;

    protected static ?Repository $requestCache = null;
    protected static ?Repository $dbCache = null;

    public static function db(): Repository
    {
        if (!static::$dbCache) {
            static::$dbCache = new Repository(new DatabaseStore(Capsule::connection(), self::TABLE_NAME));
        }

        return static::$dbCache;
    }

    public static function request(): Repository
    {
        if (!static::$requestCache) {
            static::$requestCache = new Repository(new ArrayStore());
        }

        return static::$requestCache;
    }

    public static function boot(): void
    {
        $currentVersion = Registrars::query()
            ->where('registrar', 'realtimeregister')
            ->where('setting', 'active_version')
            ->value('value');

        if (!$currentVersion || version_compare($currentVersion, App::VERSION)) {
            Capsule::table(self::TABLE_NAME)->truncate();
        }
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $value = self::db()->getStore()->get($key);
        $decoded = json_decode($value, true);
        if ($decoded !== null) {
            return $decoded;
        }

        if (is_null($value)) {
            $value = value($default);
        }

        return $value;
    }

    /**
     * Store an item in the cache.
     *
     * @param  mixed $value
     * @param  \DateTime|int $minutes
     * @return void
     */
    public static function put(string $key, $value, $minutes)
    {
        self::db()->getStore()->put($key, json_encode($value), $minutes);
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param  \DateTime|int $minutes
     * @return mixed
     */
    public static function remember(string $key, $minutes, \Closure $callback)
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
}

<?php

namespace RealtimeRegisterDomains\Models\RealtimeRegister;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\DatabaseStore;
use Illuminate\Cache\Repository;
use Illuminate\Database\Capsule\Manager as Capsule;

class Cache
{
    public const TABLE_NAME = 'mod_realtimeregister_cache';
    protected $table = self::TABLE_NAME;

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
        if (!Capsule::schema()->hasTable(Cache::TABLE_NAME)) {
            Capsule::schema()->create(
                Cache::TABLE_NAME,
                function ($table) {
                    $table->string('key')->unique();
                    $table->mediumText('value');
                    $table->integer('expiration');
                }
            );
        } else {
            // Update existing table to use mediumText for value column
            try {
                Capsule::schema()->table(Cache::TABLE_NAME, function ($table) {
                    $table->mediumText('value')->change();
                });
            } catch (\Exception $e) {
                // Ignore errors, as the column might already be MEDIUMTEXT
            }
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
     * @param  mixed         $value
     * @return void
     */
    public static function put(string $key, $value, int $minutes)
    {
        self::db()->getStore()->put($key, json_encode($value), ($minutes * 60));
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
}

<?php

namespace RealtimeRegister;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\DatabaseStore;
use Illuminate\Cache\Repository;
use Illuminate\Database\Capsule\Manager as Capsule;
use RealtimeRegister\Models\Registrars;

class Cache
{
    public const TABLE_CACHE = 'mod_realtimeregister_cache';

    protected static ?Repository $requestCache = null;
    protected static ?Repository $dbCache = null;

    public static function db(): Repository
    {
        if (!static::$dbCache) {
            static::$dbCache = new Repository(new DatabaseStore(Capsule::connection(), self::TABLE_CACHE));
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
        if (!Capsule::schema()->hasTable(self::TABLE_CACHE)) {
            Capsule::schema()->create(self::TABLE_CACHE, function ($table) {
                $table->string('key')->unique();
                $table->text('value');
                $table->integer('expiration');
            });
        } else {
            $currentVersion = Registrars::query()
                ->where('registrar', 'realtimeregister')
                ->where('setting', 'active_version')
                ->value('value');

            if (!$currentVersion || version_compare($currentVersion, App::VERSION)) {
                Capsule::table(self::TABLE_CACHE)->truncate();
            }
        }
    }
}

<?php

namespace RealtimeRegister;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use RealtimeRegister\Contracts\InvokableAction;
use RealtimeRegister\Contracts\InvokableHook;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Entities\RegistrarConfig;
use RealtimeRegister\Exceptions\ActionFailedException;
use RealtimeRegister\Services\Assets;
use RealtimeRegister\Services\ContactService;
use RuntimeException;
use SandwaveIo\RealtimeRegister\IsProxy;
use SandwaveIo\RealtimeRegister\RealtimeRegister;

// TODO https://dev.to/vimuth7/singleton-pattern-in-php-and-dependency-injection-di-container-in-laravel-4nj9
class App
{
    public const NAME = 'realtimeregister';
    public const VERSION = '2.0.0';

    protected const TABLE_CONTACT_MAPPING = 'mod_realtimeregister_contact_mapping';
    protected const TABLE_REGISTRANT_HANDLE = 'mod_realtimeregister_registrant_handle';

    protected const API_URL = "https://api.yoursrs.com/";
    protected const API_URL_TEST = "https://api.yoursrs-ote.com/";
    protected const IS_PROXY_HOST = "is.yoursrs.com";
    protected const IS_PROXY_HOST_TEST = "is.yoursrs-ote.com";

    protected readonly LocalApi $localApi;
    protected readonly RegistrarConfig $registrarConfig;
    protected readonly ContactService $contactService;

    protected static ?App $instance = null;
    protected static ?RealtimeRegister $client = null;
    protected static ?IsProxy $isProxy = null;

    protected static bool $booted = false;
    protected Assets $assets;

    public function __construct()
    {
        $this->localApi = new LocalApi();
        $this->registrarConfig = new RegistrarConfig();
        $this->contactService = new ContactService();
        $this->assets = new Assets();
    }

    public static function boot(): App
    {
        $app = static::instance();

        if (!static::$booted) {
//            Cache::boot();

//            $app->ensureTablesExist();

            static::$booted = true;
        }

        return $app;
    }

    public static function localApi(): LocalApi
    {
        return static::instance()->localApi;
    }

    public static function registrarConfig(): RegistrarConfig
    {
        return static::instance()->registrarConfig;
    }

    public static function assets(): Assets
    {
        return static::instance()->assets;
    }

    public static function contacts(): ContactService
    {
        return static::instance()->contactService;
    }

    public static function instance(): App
    {
        if (static::$instance) {
            return static::$instance;
        }

        return static::$instance = new self();
    }

    public static function client(): RealtimeRegister
    {
        if (static::$client) {
            return static::$client;
        }

        return static::$client = new RealtimeRegister(
            apiKey: App::registrarConfig()->apiKey(),
            baseUrl: App::registrarConfig()->isTest() ? self::API_URL_TEST : self::API_URL
        );
    }

    public static function isProxy(): IsProxy
    {
        if (static::$isProxy) {
            return static::$isProxy;
        }

        return static::$isProxy = new IsProxy(
            apiKey: App::registrarConfig()->apiKey(),
            host: App::registrarConfig()->isTest() ? self::IS_PROXY_HOST_TEST : self::IS_PROXY_HOST
        );
    }

    protected function dispatchTo(string $action, array $params = [])
    {
        if (($pos = strpos($action, '_')) !== false) {
            $action = substr($action, $pos + 1);
        }

        if (!class_exists($action)) {
            $action = '\RealtimeRegister\Actions\\' . ucfirst($action);
        }

        if (!class_exists($action)) {
            throw new RuntimeException('Class ' . $action . ' does not exists.');
        }

        if (!is_subclass_of($action, InvokableAction::class)) {
            throw new RuntimeException('Class ' . $action . ' does not implement the InvokableAction contract.');
        }

        $request = new Request($params);

        $this->registrarConfig->setRequest($request);

        $object = new $action($this);

        return $object($request);
    }

    public static function dispatch(string $action, array $params = [], callable $catch = null)
    {
        try {
            return static::instance()->dispatchTo($action, $params);
        } catch (\Throwable $exception) {
            if ($catch) {
                return $catch($exception);
            }

            if ($exception instanceof ActionFailedException) {
                return $exception->response($action);
            }

            return ActionFailedException::forException($exception)
                ->response($action);
        }
    }

    public static function dispatchHook(string $hook, array $arguments = [])
    {
        if (!class_exists($hook)) {
            $hook = '\RealtimeRegister\Hooks\\' . ucfirst($hook);
        }

        if (!class_exists($hook)) {
            throw new RuntimeException('Class ' . $hook . ' does not exists.');
        }

        if (!is_subclass_of($hook, InvokableHook::class)) {
            throw new RuntimeException('Class ' . $hook . ' does not implement the InvokableHook contract.');
        }

        $object = new $hook(App::instance());

        return $object(new DataObject($arguments));
    }

    public static function hook(string $name, string $hook = null, int $priority = 1): void
    {
        if (class_exists($name) && is_subclass_of($name, InvokableHook::class)) {
            $hook = $name;
            $name = class_basename($name);
        }

        add_hook($name, $priority, fn (array $vars = []) => static::dispatchHook($hook ?: $name, $vars));
    }

    protected function ensureTablesExist(): void
    {
        if (!Capsule::schema()->hasTable(self::TABLE_CONTACT_MAPPING)) {
            Capsule::schema()->create(self::TABLE_CONTACT_MAPPING, function (Blueprint $table) {
                $table->integer('userid');
                $table->integer('contactid');
                $table->char('handle', 40);
                $table->boolean('org_allowed');
                $table->unique(['userid', 'contactid', 'org_allowed'], 'mod_realtimeregister_contact_mapping_unique_contact');
                $table->unique('handle', 'mod_realtimeregister_contact_mapping_unique_handle');
            });
        }
    }
}

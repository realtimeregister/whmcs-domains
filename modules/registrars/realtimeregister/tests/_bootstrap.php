<?php

// phpcs:disable PSR1.Files.SideEffects

// This is the bootstrap for PHPUnit testing.
if (!defined('WHMCS')) {
    define('WHMCS', true);
}

if (!defined('ROOTDIR')) {
    define('ROOTDIR', __DIR__ . '/../../../../');
}

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();

$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);

// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

$capsule->setEventDispatcher(new Dispatcher(new Container()));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

// Initialize database schema for testing
// Create Cache table
\RealtimeRegisterDomains\Models\RealtimeRegister\Cache::boot();

// Create ContactMapping table if it doesn't exist
if (!$capsule::schema()->hasTable(\RealtimeRegisterDomains\Models\RealtimeRegister\ContactMapping::TABLE_NAME)) {
    $capsule::schema()->create(
        \RealtimeRegisterDomains\Models\RealtimeRegister\ContactMapping::TABLE_NAME,
        function ($table) {
            $table->integer('userid');
            $table->integer('contactid');
            $table->char('handle', 40);
            $table->boolean('org_allowed');
            $table->unique(
                ['userid', 'contactid', 'org_allowed'],
                'mod_realtimeregister_contact_mapping_unique_contact'
            );
            $table->unique('handle', 'mod_realtimeregister_contact_mapping_unique_handle');
        }
    );
}

// Include the WHMCS module.
require_once __DIR__ . '/../realtimeregister.php';

/**
 * Mock logModuleCall function for testing purposes.
 *
 * Inside of WHMCS, this function provides logging of module calls for debugging
 * purposes. The module log is accessed via Utilities > Logs.
 *
 * @param string $module
 * @param string $action
 * @param string|array $request
 * @param string|array $response
 * @param string|array $data
 * @param array $variablesToMask
 *
 * @return void|false
 */
function logModuleCall(
    $module,
    $action,
    $request,
    $response,
    $data = '',
    $variablesToMask = array()
) {
    // do nothing during tests
}

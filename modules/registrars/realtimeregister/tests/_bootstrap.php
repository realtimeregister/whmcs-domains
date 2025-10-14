<?php

// phpcs:disable PSR1.Files.SideEffects

// This is the bootstrap for PHPUnit testing.
if (!defined('WHMCS')) {
    define('WHMCS', true);
}

new \RealtimeRegisterDomains\Services\Language(); // Load our own language strings before anything else

global $_LANG;
// These are mock classes, we don't want to include WHMCS itself..
require_once __DIR__ . '/Mock/WHMCS/Module/AbstractWidget.php';
require_once __DIR__ . '/Mock/WHMCS/Config/Setting.php';

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

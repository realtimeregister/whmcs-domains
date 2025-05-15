<?php

/**
 * Perform any initialization required by the service's library.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\RealtimeregisterDns\Client\ClientDispatcher;
use WHMCS\Module\Addon\RealtimeregisterDns\ConfigArray;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define addon module configuration parameters.
 */
function realtimeregister_dns_config()
{
    return (new ConfigArray())();
}

/**
 * Client Area Output.
 *
 * Called when the addon module is accessed via the client area.
 *
 * @see AddonModule\Client\Controller::index()
 *
 */
function realtimeregister_dns_clientarea($vars)
{
    $isActive = Capsule::table('tbladdonmodules')->select('value')->where('module', 'realtimeregister_dns')->where(
        'setting',
        'active'
    )->first();

    if ($isActive->value == 'on') {
        /**
         * Dispatch and handle request here. What follows is a demonstration of one
         * possible way of handling this using a very basic dispatcher implementation.
         */
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

        $vars['domainId'] = $_REQUEST['id'];
        $vars['clientId'] = $_SESSION['uid']; // might be wrong, we need clientid?
        $vars['action'] = $action;

        $dispatcher = new ClientDispatcher();
        return $dispatcher->dispatch($action, $vars);
    }
}

<?php

use RealtimeRegister\Actions\CheckAvailability;
use RealtimeRegister\Actions\GetContactDetails;
use RealtimeRegister\Actions\GetDomainInformation;
use RealtimeRegister\Actions\GetNameservers;
use RealtimeRegister\Actions\GetRegistrarLock;
use RealtimeRegister\Actions\SaveContactDetails;
use RealtimeRegister\Actions\SaveNameservers;
use RealtimeRegister\Actions\SaveRegistrarLock;
use RealtimeRegister\Actions\Sync;
use RealtimeRegister\App;
use RealtimeRegister\ConfigArray;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/vendor/autoload.php';
//require_once ROOTDIR. '/includes/registrarfunctions.php';

$app = App::boot();

function realtimeregister_version(): string
{
    return App::VERSION;
}

function realtimeregister_ConfigOptions(): array
{
    return (new ConfigArray())();
}

function realtimeregister_CheckAvailability(array $params)
{
    return App::dispatch(CheckAvailability::class, $params);
}

function realtimeregister_GetDomainInformation(array $params)
{
    return App::dispatch(GetDomainInformation::class, $params);
}

//function realtimeregister_GetNameservers(array $params)
//{
//    return App::dispatch(GetNameservers::class, $params);
//}

function realtimeregister_SaveNameservers(array $params)
{
    return App::dispatch(SaveNameservers::class, $params);
}

//function realtimeregister_GetRegistrarLock(array $params)
//{
//    return App::dispatch(GetRegistrarLock::class, $params);
//}

function realtimeregister_SaveRegistrarLock(array $params)
{
    return App::dispatch(SaveRegistrarLock::class, $params);
}

function realtimeregister_GetContactDetails(array $params)
{
    return App::dispatch(GetContactDetails::class, $params);
}

function realtimeregister_SaveContactDetails(array $params)
{
    return App::dispatch(SaveContactDetails::class, $params);
}

function realtimeregister_Sync(array $params)
{
    return App::dispatch(Sync::class, $params);
}

function realtimeregister_AdminCustomButtonArray(array $params): array
{
    return [
        "Sync domain" => "Sync"
    ];
}

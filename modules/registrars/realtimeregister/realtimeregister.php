<?php

// phpcs:disable PSR1.Files.SideEffects

use RealtimeRegister\Actions\Contacts\GetContactDetails;
use RealtimeRegister\Actions\Contacts\SaveContactDetails;
use RealtimeRegister\Actions\Domains\CheckAvailability;
use RealtimeRegister\Actions\Domains\GetDomainInformation;
use RealtimeRegister\Actions\Domains\SaveNameservers;
use RealtimeRegister\Actions\Domains\SaveRegistrarLock;
use RealtimeRegister\Actions\Domains\Sync;
use RealtimeRegister\App;
use RealtimeRegister\ConfigArray;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/vendor/autoload.php';
require_once ROOTDIR . '/includes/registrarfunctions.php';

$app = App::boot();

function realtimeregister_version(): string
{
    return App::VERSION;
}

function realtimeregister_getConfigArray(): array
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
    return App::dispatch(\RealtimeRegister\Hooks\AdminCustomButtonArray::class, $params);
}

function realtimeregister_RegisterDomain(array $params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\RegisterDomain::class, $params);
}

function realtimeregister_GetTldPricing(array $params)
{
    return App::dispatch(\RealtimeRegister\Actions\Tlds\PricingSync::class, $params);
}

// Custom functions
function realtimeregister_SyncExpiryDate($params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\SyncExpiryDate::class, $params);
}

function realtimeregister_RegisterWithBillables(array $params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\RegisterWithBillables::class, $params);
}

function realtimeregister_TransferWithBillables($params)
{
    throw new Exception(__FUNCTION__ . ' is not implemented yet');
}

function realtimeregister_ResendTransfer(array $params)
{
    throw new Exception(__FUNCTION__ . ' is not implemented yet');
}

function realtimeregister_ResendValidationMails(array $params)
{
    throw new Exception(__FUNCTION__ . ' is not implemented yet');
}

function realtimeregister_GetEPPCode($params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\GetAuthCode::class, $params);
}

function realtimeregister_RequestDelete($params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\Delete::class, $params);
}

function realtimeregister_RenewDomain($params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\RenewDomain::class, $params);
}

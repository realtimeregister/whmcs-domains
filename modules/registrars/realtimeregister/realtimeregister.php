<?php

// phpcs:disable PSR1.Files.SideEffects

use RealtimeRegister\Actions\Contacts\GetContactDetails;
use RealtimeRegister\Actions\Contacts\ResendValidation;
use RealtimeRegister\Actions\Contacts\SaveContactDetails;
use RealtimeRegister\Actions\Domains\CheckAvailability;
use RealtimeRegister\Actions\Domains\GetDomainInformation;
use RealtimeRegister\Actions\Domains\ResendTransfer;
use RealtimeRegister\Actions\Domains\SaveNameservers;
use RealtimeRegister\Actions\Domains\SaveRegistrarLock;
use RealtimeRegister\Actions\Domains\Sync;
use RealtimeRegister\Actions\Domains\TransferWithBillables;
use RealtimeRegister\App;
use RealtimeRegister\ConfigArray;
use RealtimeRegister\Exceptions\ActionFailedException;
use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domains\DomainLookup\SearchResult;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/vendor/autoload.php';
require_once ROOTDIR . '/includes/registrarfunctions.php';

new \RealtimeRegister\Services\Language(); // Load our own language strings before anything else

$app = App::boot();

function realtimeregister_version(): string
{
    return App::VERSION;
}

function realtimeregister_getConfigArray(): array
{
    return (new ConfigArray())();
}

function realtimeregister_config_validate(array $params)
{
    return App::dispatch(
        action:\RealtimeRegister\Actions\ConfigurationValidation::class,
        params: $params,
        catch: [\RealtimeRegister\Actions\ConfigurationValidation::class, 'handleException']
    );
}

function realtimeregister_CheckAvailability(array $params): ResultsList
{
    return App::dispatch(
        CheckAvailability::class,
        $params,
        ['RealtimeRegister\Actions\Domains\CheckAvailability', 'handleException']
    );
}

function realtimeregister_GetDomainInformation(array $params)
{
    return App::dispatch(GetDomainInformation::class, $params);
}

function realtimeregister_SaveNameservers(array $params)
{
    return ActionFailedException::forException( new \Exception("fuckup"))->response("a");
    return App::dispatch(SaveNameservers::class, $params);
}

function realtimeregister_GetRegistrarLock(array $params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\GetRegistrarLock::class, $params);
}

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
    return App::dispatch(
        \RealtimeRegister\Hooks\AdminCustomButtonArray::class,
        $params,
        ['\RealtimeRegister\Hooks\AdminCustomButtonArray', 'handleException']
    );
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
    return App::dispatch(TransferWithBillables::class, $params);
}

function realtimeregister_ResendTransfer(array $params)
{
    return App::dispatch(ResendTransfer::class, $params);
}

function realtimeregister_ResendValidationMails(array $params)
{
    return App::dispatch(ResendValidation::class, $params);
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

function realtimeregister_TransferDomain($params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\TransferDomain::class, $params);
}

function realtimeregister_IDProtectToggle($params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\IDProtection::class, $params);
}

function realtimeregister_ClientAreaCustomButtonArray($params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\ClientAreaCustomButtonArray::class, $params);
}

function realtimeregister_ClientArea($params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\ClientArea::class, $params);
}

function realtimeregister_ChildHosts($params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\ChildHosts::class, $params);
}

function realtimeregister_DNSSec($params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\DNSSec::class, $params);
}

function realtimeregister_TransferSync($params)
{
    return App::dispatch(\RealtimeRegister\Actions\Domains\TransferSync::class, $params);
}

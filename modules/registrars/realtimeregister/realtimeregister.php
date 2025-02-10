<?php

// phpcs:disable PSR1.Files.SideEffects

use RealtimeRegisterDomains\Actions\Contacts\GetContactDetails;
use RealtimeRegisterDomains\Actions\Contacts\ResendValidation;
use RealtimeRegisterDomains\Actions\Contacts\SaveContactDetails;
use RealtimeRegisterDomains\Actions\Domains\CheckAvailability;
use RealtimeRegisterDomains\Actions\Domains\GetDomainInformation;
use RealtimeRegisterDomains\Actions\Domains\RegisterDomain;
use RealtimeRegisterDomains\Actions\Domains\ResendTransfer;
use RealtimeRegisterDomains\Actions\Domains\SaveNameservers;
use RealtimeRegisterDomains\Actions\Domains\SaveRegistrarLock;
use RealtimeRegisterDomains\Actions\Domains\Sync;
use RealtimeRegisterDomains\Actions\Domains\TransferDomain;
use RealtimeRegisterDomains\Actions\Domains\TransferWithBillables;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\ConfigArray;
use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domains\DomainLookup\SearchResult;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/vendor/autoload.php';
require_once ROOTDIR . '/includes/registrarfunctions.php';
if (file_exists(__DIR__ . '/customhooks.php')) {
    require_once __DIR__ . '/customhooks.php';
}

new \RealtimeRegisterDomains\Services\Language(); // Load our own language strings before anything else

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
        action:\RealtimeRegisterDomains\Actions\ConfigurationValidation::class,
        params: $params,
        catch: [\RealtimeRegisterDomains\Actions\ConfigurationValidation::class, 'handleException']
    );
}

function realtimeregister_CheckAvailability(array $params): ResultsList
{
    return App::dispatch(CheckAvailability::class, $params, [CheckAvailability::class, 'handleException']);
}

function realtimeregister_GetDomainInformation(array $params)
{
    return App::dispatch(GetDomainInformation::class, $params);
}

function realtimeregister_SaveNameservers(array $params)
{
    return App::dispatch(SaveNameservers::class, $params);
}

function realtimeregister_GetRegistrarLock(array $params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\GetRegistrarLock::class, $params);
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

function realtimeregister_ManualSync(array $params)
{
    return App::dispatch(Sync::class, [...$params, 'persist' => true]);
}

function realtimeregister_AdminCustomButtonArray(array $params): array
{
    return App::dispatch(
        \RealtimeRegisterDomains\Hooks\AdminCustomButtonArray::class,
        $params,
        [\RealtimeRegisterDomains\Hooks\AdminCustomButtonArray::class, 'handleException']
    );
}

function realtimeregister_RegisterDomain(array $params)
{
    return App::dispatch(RegisterDomain::class, $params);
}

function realtimeregister_GetTldPricing(array $params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Tlds\PricingSync::class, $params);
}

function realtimeregister_RegisterWithBillables(array $params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\RegisterWithBillables::class, $params);
}

function realtimeregister_RenewDomainWithBillables(array $params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\RenewDomainWithBillables::class, $params);
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
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\GetAuthCode::class, $params);
}

function realtimeregister_RequestDelete($params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\Delete::class, $params);
}

function realtimeregister_RenewDomain($params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\RenewDomain::class, $params);
}

function realtimeregister_TransferDomain($params)
{
    return App::dispatch(TransferDomain::class, $params, [TransferDomain::class, 'handleException']);
}

function realtimeregister_IDProtectToggle($params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\IDProtection::class, $params);
}

function realtimeregister_ClientAreaCustomButtonArray($params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\ClientAreaCustomButtonArray::class, $params);
}

function realtimeregister_ClientArea($params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\ClientArea::class, $params);
}

function realtimeregister_ChildHosts($params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\ChildHosts::class, $params);
}

function realtimeregister_DNSSec($params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\DNSSec::class, $params);
}

function realtimeregister_TransferSync($params)
{
    return App::dispatch(\RealtimeRegisterDomains\Actions\Domains\TransferSync::class, $params);
}

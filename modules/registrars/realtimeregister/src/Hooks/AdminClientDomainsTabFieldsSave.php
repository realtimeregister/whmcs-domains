<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegisterDomains\Actions\Domains\DomainContactTrait;
use RealtimeRegisterDomains\Actions\Domains\DomainTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;
use RealtimeRegisterDomains\Models\Whmcs\AdditionalFields;
use RealtimeRegisterDomains\Services\ContactService;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;
use TrueBV\Punycode;


class AdminClientDomainsTabFieldsSave extends Hook
{
    use DomainTrait;
    use DomainContactTrait;

    /**
     * @throws \Exception
     */
    public function __invoke(DataObject $vars): void
    {
        $domain = App::localApi()->domain($vars['userid'], $vars['id']);
        $metadata = (new MetadataService((new Punycode())->encode($domain['domainname'])));
        $metadataProperties = $metadata->getMetadata()->contactProperties?->toArray();

        if (!$metadataProperties) {
            return;
        }

        $newProperties = array_combine(
            array_column($metadataProperties, 'name'),
            $vars['domainfield']
        );

        $order = App::localApi()->order($domain['orderid']);
        $handle = ContactService::getContactMapping(
            $vars['userid'],
            $order['contactid'],
            $metadata->getMetadata()->registrant->organizationAllowed
        )?->handle;

        if ($handle) {
            try {
                self::addProperties($newProperties, $handle, $metadata->getAll());
            } catch (\Exception $e) {
                LogService::logError($e);
                self::revertChanges($handle, $metadata->getAll(), $vars['id']);
                throw $e;
            }
        }
    }

    private static function revertChanges($handle, $tldInfo, $domainId): void
    {
        $currentContact = App::client()->contacts->get(App::registrarConfig()->customerHandle(), $handle);
        $currentProperties = ($currentContact->properties ?? [])[$tldInfo->provider] ?? [];
        foreach ($currentProperties as $key => $value) {
            AdditionalFields::query()
                ->where('domainid', '=', $domainId)
                ->where('name', '=', $key)
                ->update(['value' => $value]);
        }
    }
}

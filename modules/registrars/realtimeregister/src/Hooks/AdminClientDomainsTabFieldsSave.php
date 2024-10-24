<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\Actions\Domains\DomainContactTrait;
use RealtimeRegisterDomains\Actions\Domains\DomainTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Models\Whmcs\AdditionalFields;
use RealtimeRegisterDomains\Services\ContactService;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;

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

        // See if the domain is still in our portfolio
        if ($domain && $domain->get('registrar') === 'realtimeregister') {
            $metadata = (new MetadataService(App::toPunyCode($domain['domainname'])));
            $metadataProperties = $metadata->getMetadata()->contactProperties?->toArray();

            if (!$metadataProperties || !$vars['domainfield']) {
                return;
            }

            $newProperties = array_filter(
                array_combine(array_column(self::getFieldNames($vars['id']), 'name'), $vars['domainfield'] ?? []),
                fn($key) => $key !== 'languageCode',
                ARRAY_FILTER_USE_KEY
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
    }

    private static function getFieldNames(int $domainId): array
    {
        return AdditionalFields::query()
            ->select(["name"])
            ->where("domainid", '=', $domainId)
            ->get()
            ->toArray();
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

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

            if (
                (count($metadataProperties) !== count($vars['domainfield']))
                || (array_keys($metadataProperties) !== array_keys($vars['domainfield']))
            ) {
                // We load the original files, this tells us how many fields we need to skip in the resulting
                // domainfields array.
                $res = (new \WHMCS\Domains\AdditionalFields())->setDomain($domain['domainname']);

                $originalFields = $res->getFields();
                $currentIdx = count($originalFields);

                $lastIdx = max(
                    array_key_last(self::getFieldNames($vars['id'])),
                    (count($metadataProperties) + count($originalFields))
                );

                for (; $currentIdx < $lastIdx; $currentIdx++) {
                    if (!array_key_exists($currentIdx, $vars['domainfield'])) {
                        /**
                         * Most probably a checkbox, which doesn't get send because of an empty value when it's not
                         * selected, so we reserve its place in the array
                         */
                        $vars['domainfield'][$currentIdx] = '';
                    }
                }
            }

            ksort($vars['domainfield']);

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
                    $_SESSION['currentError'] = $e->getMessage();
                    self::revertChanges($handle, $metadata->getAll(), $vars['id']);
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

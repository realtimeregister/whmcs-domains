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
        if ($domain && $domain->get('registrar') === App::NAME) {
            $metadata = (new MetadataService(App::toPunyCode($domain['domainname'])));
            $metadataProperties = $metadata->getMetadata()->contactProperties?->toArray();

            if (!$metadataProperties || !$vars['domainfield']) {
                return;
            }

            // The original fields are keyed by the same indexes the admin form submits
            $originalFields = (new \WHMCS\Domains\AdditionalFields())->setDomain($domain['domainname'])->getFields();
            $newProperties = self::mapDomainFields($originalFields, $vars['domainfield']);

            if ($newProperties === null) {
                LogService::logError(new \Exception(
                    'Submitted domain fields for ' . $domain['domainname'] . ' do not match the additional fields'
                    . ' definitions, contact properties are not synchronized'
                ));
                return;
            }

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

    /**
     * Maps submitted values to field names by their form index, which can have gaps (e.g. removed default fields).
     * Returns null when a submitted index has no field definition, so no value ends up at the wrong property.
     */
    public static function mapDomainFields(array $originalFields, array $submitted): ?array
    {
        if (array_diff_key($submitted, $originalFields)) {
            return null;
        }

        $properties = [];
        foreach ($originalFields as $idx => $field) {
            if (!empty($field['Remove']) || $field['Name'] === 'languageCode') {
                continue;
            }
            // An unchecked checkbox is not submitted at all
            $properties[$field['Name']] = $submitted[$idx] ?? '';
        }
        return $properties;
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

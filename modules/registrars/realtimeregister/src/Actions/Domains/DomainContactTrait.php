<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\TLDInfo;
use RealtimeRegister\Domain\TLDMetaData;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Hooks\CustomHandlesTrait;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\MetadataService;

trait DomainContactTrait
{
    use CustomHandlesTrait;
    use DomainTrait;

    /**
     * Add properties to contact if necessary
     */
    protected static function addProperties(array $contactProperties, string $handle, TLDInfo $tldInfo): void
    {
        $customer = App::registrarConfig()->customerHandle();
        if (empty($contactProperties)) {
            return;
        }

        $currentContact = App::client()->contacts->get($customer, $handle);
        $currentProperties = ($currentContact->properties ?? [])[$tldInfo->provider] ?? [];
        $newProperties = self::getNewProperties($contactProperties, $tldInfo->metadata);

        if (empty($currentProperties) && !empty($newProperties)) {
            App::client()->contacts->addProperties($customer, $handle, $tldInfo->provider, $newProperties);
            return;
        }

        if (!empty($currentProperties) && $currentProperties != $newProperties) {
            try {
                App::client()->contacts->updateProperties($customer, $handle, $tldInfo->provider, $newProperties);
            } catch (\Exception) {
                return; // We might come here when we try to create contacts without the required fields
            }
        }
    }

    protected static function getNewProperties(array $contactProperties, TLDMetaData $metadata): array
    {
        $metadataProperties = $metadata->contactProperties?->toArray();
        if (!$metadataProperties) {
            return [];
        }
        $tldProperties = array_combine(array_column($metadataProperties, 'name'), $metadataProperties);
        $properties = [];

        foreach ($contactProperties as $property => $value) {
            if ($property == 'languageCode' || !isset($tldProperties[$property])) {
                continue;
            }
            if (MetadataService::isBool($tldProperties[$property]['values'] ?? [])) {
                $value = MetadataService::getPropertyBoolValue($tldProperties[$property]['values'], $value == 'on');
            }
            if (!empty($value) && array_key_exists($property, $tldProperties)) {
                $properties[$property] = $value;
            }
        }
        return $properties;
    }

    protected function generateContactsForDomain(Request $request, TLDMetaData $metadata): array
    {
        $clientId = $request->get('clientid') ?? $request->get('userid');
        $tldInfo = $this->tldInfo($request);
        // Check if we even need nameservers
        $orderId = App::localApi()->domain(
            clientId: $clientId,
            domainId: $request->get('domainid')
        )->get('orderid');
        $contactId = App::localApi()->order(id: $orderId)->get('contactid');

        $contacts = [];

        $registrant = $this->getOrCreateContact(
            clientId: $clientId,
            contactId: $contactId,
            organizationAllowed: $metadata->registrant->organizationAllowed,
            role: 'REGISTRANT'
        );

        self::addProperties($request->domain->contactProperties, $registrant, $tldInfo);

        $customHandles = $this->getCustomHandles();
        foreach (self::$CONTACT_ROLES as $role => $name) {
            $organizationAllowed = $metadata->{$name}->organizationAllowed;
            if (
                array_key_exists($tldInfo->provider, $customHandles)
                && array_key_exists($name, $customHandles[$tldInfo->provider])
                && $customHandles[$tldInfo->provider][$name] !== ''
            ) {
                $contacts[] = [
                    'role' => $role,
                    'handle' => $customHandles[$tldInfo->provider][$name]
                ];
            } else {
                $handle = $this->getOrCreateContact(
                    clientId: $clientId,
                    contactId: $contactId,
                    organizationAllowed: $organizationAllowed,
                    role: $role
                );
                if (!$this->handleOverride($role)) {
                    self::addProperties($request->domain->contactProperties, $handle, $tldInfo);
                }
                $contacts[] = [
                    'role' => $role,
                    'handle' => $handle
                ];
            }
        }

        return ['contacts' => $contacts, 'registrant' => $registrant];
    }
}

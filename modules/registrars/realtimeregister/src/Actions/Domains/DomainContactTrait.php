<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\App;
use RealtimeRegister\Hooks\CustomHandlesTrait;
use RealtimeRegister\Request;
use RealtimeRegister\Services\MetadataService;
use SandwaveIo\RealtimeRegister\Domain\TLDInfo;
use SandwaveIo\RealtimeRegister\Domain\TLDMetaData;

trait DomainContactTrait
{
    /**
     * Add properties to contact if necessary
     */
    protected static function addProperties(Request $request, string $handle, TLDInfo $tldInfo): void
    {
        $customer = App::registrarConfig()->customerHandle();
        if (empty($request->domain->contactProperties)) {
            return;
        }

        $currentContact = App::client()->contacts->get($customer, $handle);
        $currentProperties = ($currentContact->properties ?? [])[$tldInfo->provider] ?? [];
        $newProperties = self::getNewProperties($request, $tldInfo->metadata);

        if (empty($currentProperties)) {
            App::client()->contacts->addProperties($customer, $handle, $tldInfo->provider, $newProperties);
            return;
        }

        if ($currentProperties != $newProperties) {
            App::client()->contacts->updateProperties($customer, $handle, $tldInfo->provider, $newProperties);
        }
    }

    protected static function getNewProperties(Request $request, TLDMetaData $metadata): array
    {
        $contactProperties = $metadata->contactProperties->toArray();
        $tldProperties = array_combine(array_column($contactProperties, 'name'), $contactProperties);
        $properties = [];

        foreach ($request->domain->contactProperties as $property => $value) {
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
        $tldInfo = $this->tldInfo($request);
        // Check if we even need nameservers
        $orderId = App::localApi()->domain(
            clientId: $request->get('clientid'),
            domainId: $request->get('domainid')
        )->get('orderid');
        $contactId = App::localApi()->order(id: $orderId)->get('contactid');

        $contacts = [];

        $registrant = $this->getOrCreateContact(
            clientId: $request->get('client_id'),
            contactId: $contactId,
            role: 'REGISTRANT',
            organizationAllowed: $metadata->registrant->organizationAllowed
        );

        $this->addProperties($request, $registrant, $tldInfo);

        foreach (self::$CONTACT_ROLES as $role => $name) {
            $organizationAllowed = $metadata->{$name}->organizationAllowed;

            if (
                in_array($tldInfo->provider, $customHandles)
                && array_key_exists($customHandles[$tldInfo->provider], $name)
            ) {
                $contacts[] = [
                    'role' => $role,
                    'handle' => $customHandles[$tldInfo->provider][$name]
                ];
            } else {
                $contacts[] = [
                    'role' => $role,
                    'handle' => $this->getOrCreateContact(
                        clientId: $request->get('client_id'),
                        contactId: $contactId,
                        role: $role,
                        organizationAllowed: $organizationAllowed
                    )
                ];
            }
        }

        return ['contacts' => $contacts, 'registrant' => $registrant];
    }
}

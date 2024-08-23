<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\App;
use RealtimeRegister\Request;
use RealtimeRegister\Services\MetadataService;
use SandwaveIo\RealtimeRegister\Domain\TLDInfo;
use SandwaveIo\RealtimeRegister\Domain\TLDMetaData;

trait DomainContactTrait
{
    /**
     * Add properties to contact if necessary
     *
     * @param  Request $request
     * @param  string  $handle
     * @param  TLDInfo $tldInfo
     * @return void
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

    /**
     * @param  MetadataService $metadata
     * @return array
     */
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
}

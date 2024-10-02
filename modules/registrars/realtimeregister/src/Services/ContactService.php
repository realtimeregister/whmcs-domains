<?php

namespace RealtimeRegister\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Entities\WhmcsContact;
use RealtimeRegister\LocalApi;
use RealtimeRegister\Models\RealtimeRegister\ContactMapping;
use SandwaveIo\RealtimeRegister\Domain\TLDInfo;

class ContactService
{
    private static $remote_contacts_cache = [];

    public static function findRemote(DataObject $contact, bool $organizationAllowed)
    {
        $params = Arr::only($contact->getArrayCopy(), ['organization', 'name', 'email', 'country']);

        if (!$organizationAllowed) {
            unset($params['organization']);
            $params['organization:null'] = '';
        }

        $params = array_merge(
            $params,
            [
                'order' => '-createdDate',
                'export' => true
            ]
        );

        return App::client()->contacts->list(
            customer: App::registrarConfig()->customerHandle(),
            parameters: $params
        )[0];
    }

    public static function getContactMapping(int $userId, int $contactId, bool $organizationAllowed): ?ContactMapping
    {
        /**
         * @noinspection PhpIncompatibleReturnTypeInspection
         */
        return ContactMapping::query()->where('userid', $userId)
            ->where('contactid', $contactId)
            ->where('org_allowed', $organizationAllowed)
            ->first();
    }

    public static function convertToRtrContact(DataObject $whmcsContact, bool $organizationAllowed): DataObject
    {
        $realtimeRegisterContact = [
            'name' => trim($whmcsContact['firstname'] . " " . $whmcsContact['lastname']),
            'addressLine' => array_values(array_filter([$whmcsContact['address1'], $whmcsContact['address2']])),
            'postalCode' => $whmcsContact['postcode'],
            'city' => $whmcsContact['city'],
            'state' => $whmcsContact['state'],
            'country' => $whmcsContact['country'],
            'email' => $whmcsContact['email'],
            'voice' => $whmcsContact['phonenumberformatted'],
        ];

        if ($organizationAllowed) {
            $realtimeRegisterContact['organization'] = $whmcsContact['organization'];
        }

        return new DataObject($realtimeRegisterContact);
    }

    /**
     * @throws \Exception
     */
    public static function getOrCreateRegistrantContact(
        int $clientId,
        int $contactId,
        bool $organizationAllowed,
        TLDInfo $tldInfo,
        ?array $properties = null
    ): ?string {
        $handle = ContactService::getRtrContactHandle(
            clientId: $clientId,
            contactId: $contactId,
            organizationAllowed: $organizationAllowed,
            properties: $properties
        );

        if ($handle) {
            // Check if we need to add properties
            if ($properties['properties']) {
                $rtrContact = App::client()->contacts->get(App::registrarConfig()->customerHandle(), $handle);

                if (!$rtrContact->properties[$properties['registry']]) {
                    App::client()->contacts->addProperties(
                        customer: App::registrarConfig()->customerHandle(),
                        handle: $handle,
                        registry: $tldInfo->provider,
                        properties: $properties
                    );
                } elseif ($rtrContact->properties[$properties['registry']] != $properties['properties']) {
                    /**
                     * Contact mapping already exists, but properties don't match. Create new contact with specified
                     * properties, but don't store the mapping.
                     */
                    return ContactService::createRtrContactFromWhmcsContact(
                        clientId: $clientId,
                        contactId: $contactId,
                        organizationAllowed: $organizationAllowed,
                        tldInfo: $tldInfo,
                        properties: $properties
                    );
                }
            }
            return $handle;
        }

        $handle = ContactService::createRtrContactFromWhmcsContact(
            clientId: $clientId,
            contactId: $contactId,
            organizationAllowed: $organizationAllowed,
            tldInfo: $tldInfo,
            properties: $properties
        );
        ContactService::storeContactMapping(
            clientId: $clientId,
            contactId: $contactId,
            handle: $handle,
            organizationAllowed: $organizationAllowed
        );

        return $handle;
    }

    /**
     * @param int $userId
     * @param int $contactId
     * @return Collection<ContactMapping>
     */
    public function fetchMappingByContactId(int $userId, int $contactId): Collection
    {
        return ContactMapping::query()->where(
            column: 'userid',
            value: $userId
        )->where(
            column: 'contactid',
            value: $contactId
        )->get();
    }

    public function fetchMappingByHandle(string $handle): ?ContactMapping
    {
        /**
         * @noinspection PhpIncompatibleReturnTypeInspection
         */
        return ContactMapping::query()->where(column: 'handle', value: $handle)->first();
    }

    public function handleHasMapping(string $handle): bool
    {
        return ContactMapping::query()->where(column: 'handle', value: $handle)->exists();
    }

    public function addContactMapping(int $clientId, int $contactId, string $handle, bool $orgAllowed): void
    {
        ContactMapping::query()->insert(
            [
                "userid" => $clientId,
                "contactid" => $contactId,
                "handle" => $handle,
                "org_allowed" => $orgAllowed
            ]
        );
    }

    /**
     * @throws \Exception
     */
    public static function getMatchingRtrContact(
        int $client_id,
        array $whmcsContact,
        bool $organizationAllowed,
        ?array $properties = null
    ): ?string {
        $matchFields = ['organization', 'name', 'email', 'country'];
        $queryParameters = array_filter(
            ContactService::convertToRtrContact(
                whmcsContact: new DataObject($whmcsContact),
                organizationAllowed: $organizationAllowed
            )->getArrayCopy(),
            function ($key) use ($matchFields) {
                return in_array($key, $matchFields);
            },
            ARRAY_FILTER_USE_KEY
        );

        if (!$organizationAllowed) {
            $queryParameters['organization:null'] = '';
        }

        $queryParameters = array_merge($queryParameters, [
            'order' => '-createdDate',
            'export' => true,
            'fields' => 'handle',
            'limit' => 100
        ]);
        $result = App::client()->contacts->list(
            customer: App::registrarConfig()->customerHandle(),
            parameters: $queryParameters
        );

        $mappedHandles = ContactMapping::all()->pluck('handle')->toArray();

        /**
         * We store the first matched contact. If properties are given, we try to find a match for these as well. If
         * found, we return the matched contact with properties. Otherwise we return the first matched contact.
         */
        $firstMatched = null;

        foreach ($result->entities as $entity) {
            // Store the entity in cache
            self::$remote_contacts_cache[$entity->handle] = $entity;

            if (in_array($entity->handle, $mappedHandles)) {
                continue;
            }

            if (!$firstMatched && (!$properties || ($properties && !$entity->properties[$properties['registry']]))) {
                $firstMatched = $entity;
            }

            if ($properties) {
                $entityProperties = $entity->properties[$properties['registry']];
                if ($entityProperties && $entityProperties == $properties['properties']) {
                    return $entity->handle;
                }
            }
        }

        return $firstMatched->handle;
    }

    public static function storeContactMapping(
        int $clientId,
        int $contactId,
        string $handle,
        bool $organizationAllowed = true
    ): void {
        $currentHandle = ContactService::getContactMapping(
            userId: $clientId,
            contactId: $contactId,
            organizationAllowed: $organizationAllowed
        );

        if ($currentHandle) {
            ContactMapping::where(
                [['userid', $clientId], ['contactid', $contactId], ['org_allowed', $organizationAllowed]]
            )->update(
                ['handle' => $handle]
            );
        } else {
            ContactMapping::insert(
                [
                    'userid' => $clientId,
                    'contactid' => $contactId,
                    'handle' => $handle,
                    'org_allowed' => $organizationAllowed
                ]
            );
        }
    }

    public static function getConfiguredRoleHandle(string $role): ?string
    {
        $params = self::getDefaultParams();

        return match ($role) {
            'ADMIN' => $params['handle'],
            'BILLING' => $params['handle_billing'],
            'TECH' => $params['handle_tech'],
            default => null,
        };
    }

    /**
     * Get default registrar config options.
     */
    public static function getDefaultParams(): array
    {
        static $params = [];

        if (empty($params)) {
            require_once ROOTDIR . "/includes/registrarfunctions.php";
            // Skip undefined notices.
            $params = getRegistrarConfigOptions('realtimeregister') + [
                    'SyncStatus' => null,
                    'SyncExpireDate' => null,
                    'SyncDueDate' => null,
                    'SyncNextInvoiceDate' => null,
                    'DueDateDiff' => 0,
                    'NextInvoiceDateDiff' => 0,
                    'ipRestrict' => null,
                ];
        }

        return $params;
    }

    /**
     * @throws \Exception
     */
    public static function getRtrContactHandle(
        int $clientId,
        int $contactId,
        bool $organizationAllowed,
        ?array $properties = null
    ): ContactMapping | string | null {
        $handle = ContactService::getContactMapping($clientId, $contactId, $organizationAllowed);

        if ($handle) {
            return $handle;
        }

        $whmcsContact = ContactService::getWhmcsContact(clientId: $clientId, contactId: $contactId);

        $handle = ContactService::getMatchingRtrContact(
            client_id: $clientId,
            whmcsContact: $whmcsContact,
            organizationAllowed: $organizationAllowed,
            properties: $properties
        );

        if ($handle) {
            ContactService::storeContactMapping(
                clientId: $clientId,
                contactId: $contactId,
                handle: $handle,
                organizationAllowed: $organizationAllowed
            );
            return $handle;
        }

        return null;
    }

    /**
     * @throws \Exception
     */
    public static function getWhmcsContact(int $clientId, int $contactId): array
    {
        if ($contactId) {
            $whmcsContact = LocalApi::getContactDetails(clientId: $clientId, contactId: $contactId);
        } else {
            $whmcsContact = LocalApi::getClient($clientId);
        }

        // Fix phone number
        $whmcsContact['phonenumberformatted'] = WhmcsContact::formatE164a(
            $whmcsContact['phonenumber'],
            $whmcsContact['country']
        );

        $returnFields = [
            'companyname',
            'firstname',
            'lastname',
            'address1',
            'address2',
            'postcode',
            'city',
            'state',
            'country',
            'email',
            'phonenumberformatted'
        ];

        foreach ($whmcsContact as $key => &$contactField) {
            if (in_array($key, $returnFields)) {
                $contactField = html_entity_decode($contactField);
            }
        }

        return array_filter($whmcsContact, function ($key) use ($returnFields) {
            return in_array($key, $returnFields);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @throws \Exception
     */
    public static function contactCreate(DataObject $rtrContact, ?TLDInfo $tldInfo, ?array $properties): string
    {
        if (App::registrarConfig()->customerHandle() !== null && App::registrarConfig()->apiKey() !== null) {
            // Generate unique contact handle
            $handle = uniqid(App::registrarConfig()->contactHandlePrefix() ?: 'srs_');

            // Filter empty values
            $rtrContact = array_filter($rtrContact->getArrayCopy());

            $rtrContact['customer'] = App::registrarConfig()->customerHandle();
            $rtrContact['handle'] = $handle;

            // Set brand
            $params = self::getDefaultParams();
            if (!empty($params['brand'])) {
                $rtrContact['brand'] = $params['brand'];
            }

            // Create contact at RTR
            App::client()->contacts->create(...$rtrContact);

            // Add properties
            if ($properties) {
                try {
                    App::client()->contacts->addProperties(
                        customer: App::registrarConfig()->customerHandle(),
                        handle: $handle,
                        registry: $tldInfo->provider,
                        properties: $properties
                    );
                } catch (\Exception $ex) {
                    try {
                        App::client()->contacts->delete(
                            customer: App::registrarConfig()->customerHandle(),
                            handle: $handle
                        );
                    } catch (\Exception) {
                        // ignore
                    }

                    throw $ex;
                }
            }

            return $handle;
        } else {
            throw new \Exception('No credentials where available to do this action!');
        }
    }

    /**
     * @throws \Exception
     */
    public static function createRtrContactFromWhmcsContact(
        int $clientId,
        int $contactId,
        bool $organizationAllowed,
        ?TLDInfo $tldInfo,
        ?array $properties = null
    ): string {
        $whmcs_contact = ContactService::getWhmcsContact(clientId: $clientId, contactId: $contactId);
        $rtr_contact = ContactService::convertToRtrContact(
            whmcsContact: new DataObject($whmcs_contact),
            organizationAllowed: $organizationAllowed
        );
        return self::contactCreate(rtrContact: $rtr_contact, tldInfo: $tldInfo, properties: $properties);
    }

    public static function convertWhmcsDomainContactToRtrContact(
        array $whmcsDomainContact,
        bool $organizationAllowed
    ): array {
        $whmcsDomainContact['phonenumberformatted'] = WhmcsContact::formatE164a(
            $whmcsDomainContact['Phone'],
            $whmcsDomainContact['Country']
        );

        $rtr_contact = [
            'organization' => $whmcsDomainContact['Company Name'],
            'name' => $whmcsDomainContact['Contact Name'],
            'addressLine' => array_values(
                array_filter([$whmcsDomainContact['Address 1'], $whmcsDomainContact['Address 2']])
            ),
            'postalCode' => $whmcsDomainContact['Postcode'],
            'city' => $whmcsDomainContact['City'],
            'state' => $whmcsDomainContact['State'],
            'country' => $whmcsDomainContact['Country'],
            'email' => $whmcsDomainContact['Email'],
            'voice' => (!empty($whmcsDomainContact['phonenumberformatted'])) ?
                $whmcsDomainContact['phonenumberformatted'] : $whmcsDomainContact['Phone'],
        ];

        if (!$organizationAllowed) {
            unset($rtr_contact['organization']);
        }

        return $rtr_contact;
    }

    /**
     * @throws \Exception
     */
    public static function getOrCreateDomainContact(
        $clientId,
        int | string $contactId,
        mixed $role,
        $tldInfo,
        bool $organizationAllowed,
        ?array $properties = null
    ): string {
        $handle = ContactService::getConfiguredRoleHandle($role);

        if (!$handle) {
            $contact = ContactService::getRtrContactHandle(
                clientId: $clientId,
                contactId: $contactId,
                organizationAllowed: $organizationAllowed,
                properties: $properties
            );

            $handle = $contact->handle;
        }

        if ($handle) {
            return $handle;
        }

        $handle = ContactService::createRtrContactFromWhmcsContact(
            clientId: $clientId,
            contactId: $contactId,
            organizationAllowed: $organizationAllowed,
            tldInfo: $tldInfo,
            properties: $properties
        );
        ContactService::storeContactMapping(
            clientId: $clientId,
            contactId: $contactId,
            handle: $handle,
            organizationAllowed: $organizationAllowed
        );

        return $handle;
    }
}

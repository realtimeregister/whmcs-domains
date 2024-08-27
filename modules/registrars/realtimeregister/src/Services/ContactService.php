<?php

namespace RealtimeRegister\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Models\RealtimeRegister\ContactMapping;
use SandwaveIo\RealtimeRegister\Domain\Contact as RTRContact;

class ContactService
{
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

    public function findByContactId($contactId)
    {
        // Find a local contact by its id
    }

    public function findLocal(RTRContact $contact)
    {
        // Search in contact mappings

        // Search with data
    }

    public static function convertToRtrContact(DataObject $whmcsContact, bool $organizationAllowed): DataObject
    {
        $rtr_contact = [
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
            $rtr_contact['organization'] = $whmcsContact['organization'];
        }

        return new DataObject($rtr_contact);
    }

    /**
     * @param string|int $userId
     * @param string|int $contactId
     * @return Collection<ContactMapping>
     */
    public function fetchMappingByContactId(string|int $userId, string|int $contactId): Collection
    {
        return ContactMapping::query()->where('userid', $userId)->where('contactid', $contactId)->get();
    }

    public function fetchMappingByHandle(string $handle): ?ContactMapping
    {
        /**
         * @noinspection PhpIncompatibleReturnTypeInspection
         */
        return ContactMapping::query()->where('handle', $handle)->first();
    }

    public function handleHasMapping(string $handle): bool
    {
        return ContactMapping::query()->where('handle', $handle)->exists();
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

    public static function getMatchingRtrContact()
    {
        //TODO if necessary
        //        App::client()->contacts->list(
        //            App::registrarConfig()->get('customer_handle'),
        //            parameters: [
        //                'order' => '-createdDate',
        //                'export' => true,
        //                'fields' => 'handle'
        //            ]
        //        )->first();
    }
}

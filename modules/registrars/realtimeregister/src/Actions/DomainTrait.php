<?php

namespace RealtimeRegister\Actions;

use RealtimeRegister\App;
use RealtimeRegister\Models\ContactMapping;
use RealtimeRegister\Services\ContactService;

trait DomainTrait
{
    protected function getOrCreateContact(int $clientId, int $contactId, string $role, bool $organizationAllowed)
    {
        // Check if we override the handle in the settings
        $handle = match ($role) {
            'ADMIN' => $this->app->registrarConfig()->get('handle'),
            'BILLING' => $this->app->registrarConfig()->get('handle_billing'),
            'TECH' => $this->app->registrarConfig()->get('handle_tech'),
            default => null,
        };

        if ($handle) {
            return $handle;
        }

        // Check if we have a contact mapping
        $handle = ContactMapping::query()
            ->where('userid', $clientId)
            ->where('contactid', $contactId)
            ->where('org_allowed', $organizationAllowed)
            ->value('handle');

        if ($handle) {
            return $handle;
        }

        // Fetch the whmcs contact
        $whmcsContact = App::localApi()->contact($clientId, $contactId);


        // Try and match the whmcs contact to a rtr contact
        // Should we even match the contact? yes / on exception only?

        // $contact = ContactService::findRemote();

        // If we do not find a match we create a new contact
        $rtrContact = ContactService::convertToRtrContact($whmcsContact, $organizationAllowed);
        $handle = uniqid(App::registrarConfig()->contactHandlePrefix() ?: '');

        App::client()->contacts->create(
            customer: App::registrarConfig()->customerHandle(),
            handle: $handle,
            name: $rtrContact->get('name'),
            addressLine: $rtrContact->get('addressLine'),
            postalCode: $rtrContact->get('postalCode'),
            city: $rtrContact->get('city'),
            country: $rtrContact->get('country'),
            email: $rtrContact->get('email'),
            voice: $rtrContact->get('voice'),
            organization: $rtrContact->get('organization'),
            state: $rtrContact->get('state')
        );

        ContactService::addContactMapping($clientId, $contactId, $handle, $organizationAllowed);
        return $handle;
    }
}

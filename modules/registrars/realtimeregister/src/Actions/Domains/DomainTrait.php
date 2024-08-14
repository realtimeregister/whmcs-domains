<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\App;
use RealtimeRegister\Models\ContactMapping;

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

        // Should we use the request domain?

        // Fetch the whmcs contact
        $whmcsContact = App::localApi()->contact($clientId, $contactId);

        if (!$whmcsContact) {
            return null;
        }

        $fields = [
            'name' => $whmcsContact->get('')
        ];

        // Try and match the whmcs contact to a rtr contact
        // Should we even match the contact?
        $contacts = App::client()->contacts->list(
            App::registrarConfig()->get('customer_handle'),
            parameters: [
                'order' => '-createdDate',
                'export' => true,
                'fields' => 'handle'
            ]
        );

        // If we do not find a match we create a new contact

        // Map the contact in the mapper

        //
    }
}

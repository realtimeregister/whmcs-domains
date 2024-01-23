<?php

namespace RealtimeRegister\Actions;

use Illuminate\Cache\CacheManager;
use RealtimeRegister\App;
use RealtimeRegister\Cache;
use RealtimeRegister\Models\ContactMapping;
use RealtimeRegister\Request;

class RenewDomain extends Action
{

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request)
    {
        $metadata = $this->metadata($request);
        $domain = $request->domain;

        $period = $request->get('regperiod') * 12;
        if (!in_array($period, $metadata->createDomainPeriods)) {
            throw new \Exception(sprintf('It is not possible to register/transfer .%s domains for that period.', $domain->tld));
        }

        // Check if we even need nameservers

        $orderId = App::localApi()->domain(clientId: $request->get('clientid'), domainId: $request->get('domainid'))->get('orderid');
        $contactId = App::localApi()->order(id: $orderId)->get('contactid');

        $contacts = [];

        foreach (['REGISTRANT', 'ADMIN', 'BILLING'] as $role) {
            $organizationAllowed = $metadata->{strtolower($role) . 'Contacts'}->organizationAllowed;

            $contacts[] = [
                'role' => $role,
                'handle' => $this->getOrCreateContact(
                    clientId: $request->get('clientid'),
                    contactId: $contactId,
                    role: $role,
                    organizationAllowed: $organizationAllowed
                )
            ];
        }

        // Fetch order id
        // Fetch contact id

        // Create contacts for domain with all roles

        $registration = App::client()->domains->register(
            domainName: $domain->domainName(),
            ns: $domain->nameservers,
            registrant: null,
            privacyProtect: null,
            period: null,
            authcode: null,
            languageCode: null,
            autoRenew: null,
            billables: null,
            skipValidation: null,
            launchPhase: null,
            zone: null,
            contacts: null,
            keyData: null,
            customer: null,
            isQuote: false
        );
    }

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
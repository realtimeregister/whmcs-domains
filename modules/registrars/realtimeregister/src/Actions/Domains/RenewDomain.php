<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;

class RenewDomain extends Action
{
    use DomainTrait;

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request)
    {
        $metadata = $this->metadata($request);
        $domain = $request->domain;

        $period = $request->get('regperiod') * 12;
        if (!in_array($period, $metadata->createDomainPeriods)) {
            throw new \Exception(
                sprintf('It is not possible to register/transfer .%s domains for that period.', $domain->tld)
            );
        }

        // Check if we even need nameservers

        $orderId = App::localApi()->domain(
            clientId: $request->get('clientid'),
            domainId: $request->get('domainid')
        )->get('orderid');
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
            customer: null,
            registrant: null,
            autoRenew: false,
            ns: $domain->nameservers,
        );
    }
}

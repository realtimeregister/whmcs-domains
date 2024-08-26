<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\DomainContactCollection;

class RegisterDomain extends Action
{
    use DomainTrait;
    use DomainContactTrait;

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request)
    {
        $tldInfo = $this->tldInfo($request);
        $metadata = $tldInfo->metadata;
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

        $registrant = $this->getOrCreateContact(
            clientId: $request->get('client_id'),
            contactId: $contactId,
            role: 'REGISTRANT',
            organizationAllowed: $metadata->registrant->organizationAllowed
        );

        $this->addProperties($request, $registrant, $tldInfo);

        foreach (self::$CONTACT_ROLES as $role => $name) {
            $organizationAllowed = $metadata->{$name}->organizationAllowed;
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

        App::client()->domains->register(
            domainName: $domain->domainName(),
            customer: App::registrarConfig()->customerHandle(),
            registrant: $registrant,
            period: $period,
            autoRenew: false,
            ns: $domain->nameservers,
            contacts: DomainContactCollection::fromArray($contacts)
        );
    }
}

<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Domain\DomainContactCollection;

class TransferDomain extends SyncExpiryDate
{
    use DomainTrait;
    use DomainContactTrait;

    public function __invoke(Request $request): array
    {
        $metadata = $this->metadata($request);
        $domain = $request->domain;

        try {
            list(
                'registrant' => $registrant,
                'contacts' => $contacts
            ) = $this->generateContactsForDomain(request: $request, metadata: $metadata);

            App::client()->domains->transfer(
                domainName: $domain->domainName(),
                customer: App::registrarConfig()->customerHandle(),
                registrant: $registrant,
                authcode: $request->eppCode,
                autoRenew: false,
                ns: App::registrarConfig()->keepNameServers() ? null : $domain->nameservers,
                contacts: DomainContactCollection::fromArray($contacts),
            );

            return ['success' => true];
        } catch (\Exception $ex) {
            return [
                'error' => sprintf(
                    'Error transferring domain %s. Error details: %s.',
                    $request->domain->domainName(),
                    $ex->getMessage()
                )
            ];
        }
    }
}

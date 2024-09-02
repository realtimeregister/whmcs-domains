<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\App;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\DomainContactCollection;

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
            ) = $this->generateContactsForDomain($request, $metadata);

            App::client()->domains->transfer(
                domainName: $domain->domainName(),
                customer: App::registrarConfig()->customerHandle(),
                registrant: $registrant,
                authcode: $request->eppCode,
                autoRenew: false,
                contacts: DomainContactCollection::fromArray($contacts)
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

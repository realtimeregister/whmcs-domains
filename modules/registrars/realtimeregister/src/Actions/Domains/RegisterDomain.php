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
    public function __invoke(Request $request): array
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

        list(
            'registrant' => $registrant,
            'contacts' => $contacts
        ) = $this->generateContactsForDomain($request, $metadata);

        App::client()->domains->register(
            domainName: $domain->domainName(),
            customer: App::registrarConfig()->customerHandle(),
            registrant:  $registrant,
            period: $period,
            autoRenew: false,
            ns: $domain->nameservers,
            contacts: DomainContactCollection::fromArray($contacts)
        );
        return ['success' => true];
    }
}

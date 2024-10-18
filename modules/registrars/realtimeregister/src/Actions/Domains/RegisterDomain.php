<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Domain\BillableCollection;
use RealtimeRegister\Domain\DomainContactCollection;
use RealtimeRegister\Domain\DomainRegistration;
use RealtimeRegister\Domain\Enum\DomainStatusEnum;

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

        $domainName = $this->checkForPunyCode($domain);

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

        $parameters = [
            'domainName' => $domainName,
            'customer' => App::registrarConfig()->customerHandle(),
            'registrant' => $registrant,
            'period' => $period,
            'autoRenew' => false,
            'ns' => $domain->nameservers,
            'contacts' => DomainContactCollection::fromArray($contacts)
        ];

        if ($domain->idnLanguage) {
            $parameters['languageCode'] = $domain->idnLanguage;
        }

        if ($request->get('premiumEnabled') === true && (int)$request->get('premiumCost') > 0) {
            $parameters['billables'] = BillableCollection::fromArray([
                [ 'action' => 'CREATE',
                    'product' => 'domain_' . $domain->tldPunyCode . '_premium',
                    'quantity' => 1
                ]
            ]);
        }

        /** @var DomainRegistration $domainRegistration */
        $domainRegistration = App::client()->domains->register(...$parameters);

        if ($domainRegistration->expiryDate) {
            return ['success' => true];
        }

        return ['pending' => true];
    }
}

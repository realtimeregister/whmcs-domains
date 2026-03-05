<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\BillableCollection;
use RealtimeRegister\Domain\DomainContactCollection;
use RealtimeRegister\Domain\DomainRegistration;
use RealtimeRegister\Domain\Enum\ZoneServiceEnum;
use RealtimeRegister\Domain\Zone;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;

class RegisterDomain extends Action
{
    use DomainTrait;
    use DomainContactTrait;
    use DNSServicesTrait;

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

        $parameters = [
            'domainName' => self::getDomainName($domain),
            'customer' => App::registrarConfig()->customerHandle(),
            'registrant' => $registrant,
            'period' => $period,
            'autoRenew' => false,
            'ns' => $domain->nameservers,
            'contacts' => DomainContactCollection::fromArray($contacts),
            'privacyProtect' => $domain->privacyProtect
        ];

        if (App::registrarConfig()->hasDnsSupport()) {
            // remove default nameservers, we set the correct ones via the zone
            unset($parameters['ns']);
            // Add a zone, we need this because some registries require nameservers on the create call
            $this->generateDnsServers();

            $dnsParameters = [
                'name' => self::getDomainName($domain),
                'service' => ZoneServiceEnum::from(App::registrarConfig()->get('dns_support'))
            ];

            if ($this->vanityNameservers !== []) {
                $dnsParameters['ns'] = $this->vanityNameservers;
            }

            App::client()->dnszones->create(...$dnsParameters);
            $parameters['zone'] = Zone::fromArray(
                ['service' => ZoneServiceEnum::from(App::registrarConfig()->get('dns_support'))->value]
            );
        }

        if ($domain->idnLanguage && $domain->isIdn) {
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

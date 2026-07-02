<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\Whmcs\Domain;
use RealtimeRegisterDomains\Request;

class ForceDNSSupport extends SaveDns
{
    use DomainTrait;
    use DNSServicesTrait;

    public function __invoke(Request $request): array
    {
        $whmcsDomain = Domain::find($request->params['domainid']);
        // Check if the domain has dns management enabled and if we support it.
        if ($whmcsDomain['dnsmanagement'] === 1 && App::registrarConfig()->hasDnsSupport() === true) {
            $this->generateDnsServers();

            try {
                $domain = $this->domainInfo($request);
            } catch (BadRequestException $e) {
                if ($e->getMessage() === 'Not found.') {
                    return ['error' => 'Domain not found'];
                }
                throw $e;
            }

            $zone = App::client()->domains->get($domain->domainName)->zone;
            if (!$zone) {
                $dnsZonePayload = $this->generateDefaultSoaRecords($domain);
                $this->attachNewZoneToDomain($domain, $dnsZonePayload);
                return ['success' => 'Zone was attached!'];
            } else {
                return ['error' => 'Zone already exists, no need to reset it'];
            }
        } else {
            return ['error' => 'Domain doesn\'t have DNS management enabled or we don\'t support it'];
        }
    }
}

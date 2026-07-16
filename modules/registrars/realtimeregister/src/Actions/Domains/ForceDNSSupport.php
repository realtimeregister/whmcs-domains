<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\Zone;
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
                try {
                    $this->attachNewZoneToDomain($domain, $dnsZonePayload);
                } catch (BadRequestException $e) {
                    $res = json_decode(substr($e->getMessage(), 13), true);
                    // If the zone already exists, we want to attach it to the domain without creating a new one
                    if (is_array($res) && $res['type'] === 'ObjectExists') {
                        $foundZones = App::client()->dnszones->list(1, null, null, ['name:eq' => $domain->domainName]);
                        if ($foundZones->count() === 1) {
                            $foundZone = $foundZones->entities[0];
                            App::client()->domains->update(
                                domainName: $domain->domainName,
                                zone: Zone::fromArray(['service' => $foundZone->service->value])
                            );
                        }
                    }
                }
                return ['success' => 'Zone was attached!'];
            } else {
                return ['error' => 'Zone already exists, no need to reset it'];
            }
        } else {
            return ['error' => 'Domain doesn\'t have DNS management enabled or we don\'t support it'];
        }
    }
}

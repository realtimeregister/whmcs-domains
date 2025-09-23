<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\DomainDetails;
use RealtimeRegister\Domain\DomainZoneRecordCollection;
use RealtimeRegister\Domain\Enum\ZoneServiceEnum;
use RealtimeRegister\Domain\Zone;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;

class SaveDns extends Action
{
    private ZoneServiceEnum $serviceType;

    public function __invoke(Request $request): array
    {
        if ($request->params['dnsmanagement'] === true && App::registrarConfig()->hasDnsSupport() === true) {
            $this->serviceType = ZoneServiceEnum::from(App::registrarConfig()->get('dns_support'));
            $domain = $this->domainInfo($request);
            $zone = App::client()->domains->get($domain->domainName)->zone;
            if (isset($_SESSION['rtr']['dns'], $_SESSION['rtr']['dns']['success'])) {
                unset($_SESSION['rtr']['dns']['success']);
            }
            if (!is_array($_POST['dns-items'])) {
                $_POST['dns-items'] = [];
            }
            // Temporary keep the items in memory
            $_SESSION['rtr']['dns']['soa'] = $_POST['soa'];
            $_SESSION['rtr']['dns']['dns-items'] = $_POST['dns-items'];

            return $this->processUpdate($zone, $domain, $_POST['soa'], $_POST['dns-items']);
        } else {
            return ['error' => 'DNS management not enabled on this domain'];
        }
    }

    private function processUpdate(?Zone $zone, DomainDetails $domain, array $soaData, array $dnsRecords): ?array
    {
        // Cleanup the data which has been inserted by our client
        foreach ($dnsRecords as $k => $data) {
            foreach ($data as $key => $value) {
                // ttl should be a number
                if ($key == 'ttl') {
                    $dnsRecords[$k]['ttl'] = (int)$value;
                }
            }
            // Only ttl when it's about an MX server
            if (($data['type'] !== 'MX' && $data['type'] !== 'SRV')) {
                unset($dnsRecords[$k]['prio']);
            } else {
                $dnsRecords[$k]['prio'] = (int)$dnsRecords[$k]['prio'];
            }

            // Clear empty rows
            if ($data['name'] == '' && $data['content'] == '') {
                unset($dnsRecords[$k]);
            }

            if ($data['name'] == '') {
                $dnsRecords[$k]['name'] = $domain->domainName;
            }

            if (!array_key_exists('ttl', $data)) {
                $dnsRecords[$k]['ttl'] = 3600;
            }
        }

        // When we do delete actions, in the frontend, the arraykey may get mangled, a simple sort fixes this problem
        sort($dnsRecords);

        try {
            $dnsZonePayload = [
                'hostMaster' => $soaData['hostmaster'],
                'refresh' => (int)$soaData['refresh'],
                'retry' => (int)$soaData['retry'],
                'expire' => (int)$soaData['expire'],
                'ttl' => (int)$soaData['ttl'],
                'records' => DomainZoneRecordCollection::fromArray($dnsRecords)
            ];
            if (!$zone) {
                App::client()->dnszones->create(
                    name: $domain->domainName,
                    service: $this->serviceType,
                    hostMaster: $soaData['hostmaster'],
                    refresh: (int)$soaData['refresh'],
                    retry: (int)$soaData['retry'],
                    expire: (int)$soaData['expire'],
                    records: DomainZoneRecordCollection::fromArray($dnsRecords),
                );
                // Enable the just created zone, and thus, enable it to the domain
                App::client()->domains->update(
                    domainName: $domain->domainName,
                    zone: Zone::fromArray(
                        [
                            'service' => $this->serviceType->value,
                        ]
                    )
                );
            } else {
                $dnsZonePayload['id'] = $zone->id;
                App::client()->dnszones->update(...$dnsZonePayload);
                $_SESSION['rtr']['dns']['success'] = true;
                // We don't need this data anymore, everything went succesfully
                unset($_SESSION['rtr']['dns']['soa']);
                unset($_SESSION['rtr']['dns']['dns-items']);
            }
        } catch (BadRequestException $exception) {
            $_SESSION['rtr']['dns']['success'] = false;
            $exceptionText = substr($exception->getMessage(), 13);
            return ['error' => json_decode($exceptionText, true)];
        }
    }
}

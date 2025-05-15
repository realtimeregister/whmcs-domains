<?php

namespace WHMCS\Module\Addon\RealtimeregisterDns\Client;

use RealtimeRegister\Domain\DomainZoneRecordCollection;
use RealtimeRegister\Domain\Zone;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegisterDomains\App;

class Controller
{
    public function index(array $vars): array
    {
        $domain = App::client()->domains->get($vars['domain']->domain);

        $nameservers = $domain->ns;
        sort($nameservers);
        if (
            ((($nameservers == ['ns1.yoursrs.com', 'ns2.yoursrs.com']) || ($nameservers === [
                            'ns1.realtimeregister-ote.com',
                            'ns2.realtimeregister-ote.com'
                        ])) && $domain->premium === false) || $domain->premium === true
        ) {
            // we're in control, domain is on our free tier
            $zone = App::client()->domains->get($vars['domain']->domain)->zone;

            if ($zone && ($zone->link == null || $zone->master != null)) {
                $zoneId = $zone->id;
                if ($zoneId) {
                    $dataFromServer = App::client()->dnszones->get($zone->id);
                    $vars['zones'] = $dataFromServer->records->toArray();
                    $vars['soa'] = [
                        'hostmaster' => $dataFromServer->hostMaster,
                        'refresh' => $dataFromServer->refresh,
                        'retry' => $dataFromServer->retry,
                        'expire' => $dataFromServer->expire,
                        'ttl' => $dataFromServer->ttl,
                    ];
                }
            }

            if ($_POST) {
                $result = $this->processUpdate($zone, $_POST['soa'], $_POST['dns-item']);

                $vars['zones'] = $_POST['dns-item'];
                $vars['soa'] = $_POST['soa'];

                if ($result['success'] === false) {
                    // we have some errors, so we reshow the page..
                    $vars['errors'] = $result['error'];
                    return $this->renderPage($vars);
                }
                $vars['success'] = true;
            }
            return $this->renderPage($vars);
        } else {
            $vars['nameservers'] = $domain->ns;
            return [
                'templatefile' => 'not-in-control',
                'vars' => $vars,
            ];
        }
    }

    private function processUpdate(?Zone $zone, array $soaData, array $dnsRecords): ?array
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

            if (!in_array('ttl', $data)) {
                $dnsRecords[$k]['ttl'] = 3600;
            }
        }

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
                $dnsZonePayload['name'] = 'records_added_by_whmcs';
                App::client()->dnszones->create(...$dnsZonePayload);
            } else {
                $dnsZonePayload['id'] = $zone->id;
                App::client()->dnszones->update(...$dnsZonePayload);
            }
            return ['success' => true];
        } catch (BadRequestException $exception) {
            $exceptionText = substr($exception->getMessage(), 13);
            // TODO logservice call
            return ['success' => false, 'error' => json_decode($exceptionText, true)];
        }
    }

    private function renderPage(array $vars): array
    {
        $vars['typesOfRecords'] = [
            'A',
            'MX',
            'CNAME',
            'AAAA',
            'URL',
            'MBOXFW',
            'HINFO',
            'NAPTR',
            'NS',
            'SRV',
            'CAA',
            'TLSA',
            'TXT',
            'ALIAS',
            'DNSKEY',
            'CERT',
            'DS',
            'LOC',
            'SSHFP',
//                    'URI',    // This is not supported on premium dns
        ];

        return [
            'pagetitle' => str_replace(':domain', $vars['domain']->domain, $vars['_lang']['dns_settings_pagetitle']),
            'breadcrumb' => [
                'index.php?m=realtimeregister_dns' => $vars['_lang']['dns_settings'],
            ],
            'templatefile' => 'overview',
            'requirelogin' => true, // We only want access for authenticated users
            'vars' => $vars,
        ];
    }

    public function notAllowed(array $vars): array
    {
        return [
            'templatefile' => 'not-found',
            'vars' => $vars,
        ];
    }
}

<?php

namespace RealtimeRegisterDomains\Hooks\Client;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;

class ClientAreaPageDomainDNSManagement extends Hook
{
    public function __invoke(DataObject $vars): array
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

        return $vars->getArrayCopy();
    }
}

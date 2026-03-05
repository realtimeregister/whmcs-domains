<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\Enum\ZoneServiceEnum;
use RealtimeRegisterDomains\App;

trait DNSServicesTrait
{
    private ZoneServiceEnum $serviceType;
    private array $vanityNameservers = [];

    private function generateDnsServers(): void
    {
        $this->serviceType = ZoneServiceEnum::from(App::registrarConfig()->get('dns_support'));
        if ($this->serviceType == ZoneServiceEnum::PREMIUM) {
            $vanityNameservers = [
                App::registrarConfig()->get('dns_vanity_nameserver_1'),
                App::registrarConfig()->get('dns_vanity_nameserver_2')
            ];

            $vanityNameservers = array_values(
                array_unique(
                    array_filter(
                        $vanityNameservers,
                        static fn($v) => $v !== ''
                    )
                )
            );

            if ($vanityNameservers !== []) {
                $this->vanityNameservers = $vanityNameservers;
            }
        }
    }
}

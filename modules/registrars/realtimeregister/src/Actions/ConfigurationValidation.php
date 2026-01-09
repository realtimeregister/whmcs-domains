<?php

namespace RealtimeRegisterDomains\Actions;

use RealtimeRegister\Domain\Enum\ZoneServiceEnum;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\LogService;
use WHMCS\Exception\Module\InvalidConfiguration;

class ConfigurationValidation extends Action
{
    public function __invoke(Request $request): void
    {
        $this->validateBrands();
        if ($request->get('dns_support') == ZoneServiceEnum::PREMIUM->value) {
            $this->validateVanityDnsServers($request);
        }
    }

    private function validateBrands(): void
    {
        $brands = App::client()->brands->list(customer: App::registrarConfig()->customerHandle());
        if ($brands->count() == 0) {
            throw new \WHMCS\Exception\Module\InvalidConfiguration(
                'Something went wrong checking your connection' .
                ' to the API of Realtime Register, please check your credentials'
            );
        }
    }

    private function validateVanityDnsServers(Request $request): void
    {
        $vanityNameServers = [
            $request->get("dns_vanity_nameserver_1"),
            $request->get("dns_vanity_nameserver_2")
        ];

        $vanityNameServers = array_values(
            array_unique(
                array_filter(
                    $vanityNameServers,
                    static fn($v) => $v !== ''
                )
            )
        );

        // They are allowed to be empty, in that case we will use our own nameservers
        if (empty($vanityNameServers) || $vanityNameServers[0] === '') {
            return;
        }

        if (count($vanityNameServers) != 2) {
            throw new \WHMCS\Exception\Module\InvalidConfiguration('Two nameservers have to be configured');
        }

        foreach ($vanityNameServers as $i => $vanityNameServer) {
            if (
                filter_var($vanityNameServer, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
                !== $vanityNameServer
                || substr_count($vanityNameServer, '.') < 2
            ) {
                throw new \WHMCS\Exception\Module\InvalidConfiguration('Problems with dns vanity server [' . $i . ']"'
                    . $vanityNameServer . '"');
            }
        }
    }

    public static function handleException(\Throwable $exception, array $params)
    {
        LogService::logError($exception);
        throw new \WHMCS\Exception\Module\InvalidConfiguration($exception->getMessage());
    }
}

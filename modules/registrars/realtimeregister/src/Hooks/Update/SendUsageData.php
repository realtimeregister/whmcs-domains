<?php

namespace RealtimeRegisterDomains\Hooks\Update;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;

class SendUsageData extends Hook
{
    public function __invoke(DataObject $vars): void
    {
        global $CONFIG;

        $information =  [
            'handle' => App::registrarConfig()->customerHandle(),
            'servername' => preg_replace('#^http(s)?://#', '', rtrim($CONFIG['SystemURL'], '/')),
            'php' => phpversion(),
            'whmcsversion' => $CONFIG['Version'],
            'module_version' => App::VERSION,
            'default_country' => $CONFIG['DefaultCountry'],
            'default_language' => $CONFIG['Language'],
            'ote' => App::registrarConfig()->isTest() ? 'true' : 'false'
        ];

        $url = App::USAGE_DATA_URL;

        if (!empty($information['handle'])) {
            $url .= '?' . http_build_query($information);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}

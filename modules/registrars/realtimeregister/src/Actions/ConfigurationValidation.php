<?php

namespace RealtimeRegisterDomains\Actions;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\LogService;
use WHMCS\Exception\Module\InvalidConfiguration;

class ConfigurationValidation extends Action
{
    public function __invoke(Request $request)
    {
        $brands = App::client()->brands->list(customer: App::registrarConfig()->customerHandle());
        if ($brands->count() > 0) {
            return;
        } else {
            throw new \WHMCS\Exception\Module\InvalidConfiguration();
        }
    }

    public static function handleException(\Throwable $exception, array $params)
    {
        if ($exception) {
            LogService::logError($exception);
            throw new \WHMCS\Exception\Module\InvalidConfiguration(
                'Something went wrong checking your connection to the API of Realtime Register, '
                . 'please check your credentials'
            );
        }
    }
}

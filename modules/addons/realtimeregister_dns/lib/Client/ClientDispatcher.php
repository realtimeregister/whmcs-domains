<?php

namespace WHMCS\Module\Addon\RealtimeregisterDns\Client;

use RealtimeRegisterDomains\Models\Whmcs\Domain;

/**
 * Client Area Dispatch Handler
 */
class ClientDispatcher
{
    public function dispatch(string $action, array $parameters): array
    {
        if (!$action) {
            // Default to index if no action specified
            $action = 'index';
        }

        $controller = new Controller();

        $parameters['domain'] = Domain::query()->where(
            ['id' => $parameters['domainId'], 'userId' => $parameters['clientId']]
        )->first();

        if ($parameters['domain'] === null) {
            return $controller->notAllowed($parameters);
        }
        // Verify requested action is valid and callable
        if (is_callable(array($controller, $action))) {
            return $controller->$action($parameters);
        }
        return [];
    }
}

<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegisterDomains\Services\LogService;

class GetNameservers extends Action
{
    public function __invoke(Request $request): array
    {
        try {
            $domain = $this->domainInfo($request);

            $nameservers = [];
            foreach ($domain->ns as $key => $server) {
                $nameservers['ns' . ($key + 1)] = $server;
            }

            return $nameservers;
        } catch (BadRequestException $exception) {
            LogService::logError($exception);
            return [
                'error' => 'Domain not registered yet.'
            ];
        }
    }
}

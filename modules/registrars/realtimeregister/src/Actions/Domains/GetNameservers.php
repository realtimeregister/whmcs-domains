<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Exceptions\BadRequestException;

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
        } catch (BadRequestException $e) {
            return [
                'error' => 'Domain not registered yet.'
            ];
        }
    }
}

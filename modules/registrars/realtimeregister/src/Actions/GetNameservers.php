<?php

namespace RealtimeRegister\Actions;

use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Exceptions\NotFoundException;

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
        } catch (NotFoundException) {
            return [
                'error' => 'Domain not registered yet.'
            ];
        }

    }
}
<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Exceptions\RealtimeRegisterClientException;

class SaveNameservers extends Action
{
    public function __invoke(Request $request): array
    {
        try {
            App::client()->domains->update(
                domainName: $request->domain->domainName(),
                ns: $request->domain->nameservers
            );

            $this->forgetDomainInfo($request);

            return ['success' => true];
        } catch (RealtimeRegisterClientException $exception) {
            return ['error' => sprintf('Error fetching domain information: %s', $exception->getMessage())];
        }
    }
}

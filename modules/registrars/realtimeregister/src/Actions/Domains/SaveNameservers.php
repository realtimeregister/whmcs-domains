<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Exceptions\RealtimeRegisterClientException;

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

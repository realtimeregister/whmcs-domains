<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;

class IDProtection extends Action
{
    public function __invoke(Request $request): array
    {
        try {
            App::client()->domains->update(
                domainName: $request->domain->domainName(),
                privacyProtect: (bool)$request->params['protectenable']
            );
        } catch (\Exception $exception) {
            return ['error' => sprintf('Error setting ID Protection on domain: %s', $exception->getMessage())];
        }
        return ['success' => true, 'ID Protection Enabled'];
    }
}

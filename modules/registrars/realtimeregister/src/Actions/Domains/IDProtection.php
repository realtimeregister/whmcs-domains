<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;

class IDProtection extends Action
{
    use DomainTrait;

    public function __invoke(Request $request): array
    {
        try {
            App::client()->domains->update(
                domainName: self::getDomainName($request->domain),
                privacyProtect: $request->domain->privacyProtect
            );
        } catch (\Exception $exception) {
            return ['error' => sprintf('Error setting ID Protection on domain: %s', $exception->getMessage())];
        }
        return ['success' => true, 'ID Protection Enabled'];
    }
}

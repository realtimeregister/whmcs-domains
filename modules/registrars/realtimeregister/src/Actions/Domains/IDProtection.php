<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;

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
            return ['error' => 'Error setting ID Protection on domain: %s', $exception->getMessage()];
        }
        return ['success' => true, 'ID Protection Enabled'];
    }
}

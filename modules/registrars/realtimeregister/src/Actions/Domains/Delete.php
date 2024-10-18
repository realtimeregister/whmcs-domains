<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;

class Delete extends Action
{
    public function __invoke(Request $request): array
    {
        App::client()->domains->delete($request->domain->domainName());
        $this->forgetDomainInfo($request);

        return ['success' => 'success'];
    }
}

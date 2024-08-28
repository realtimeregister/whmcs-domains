<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;

class Delete extends Action
{
    public function __invoke(Request $request): array
    {
        App::client()->domains->delete($request->domain->domainName());
        $this->forgetDomainInfo($request);

        return ['success' => 'success'];
    }
}

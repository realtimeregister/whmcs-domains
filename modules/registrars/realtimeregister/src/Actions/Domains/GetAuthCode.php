<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\Request;

class GetAuthCode extends Action
{
    public function __invoke(Request $request)
    {
        $domain = $this->domainInfo($request);
        return $domain->authcode;
    }
}

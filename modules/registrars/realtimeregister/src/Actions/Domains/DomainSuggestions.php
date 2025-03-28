<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\Request;
use WHMCS\Domains\DomainLookup\ResultsList;

class DomainSuggestions extends Action
{
    public function __invoke(Request $request)
    {
        return new ResultsList();
    }
}

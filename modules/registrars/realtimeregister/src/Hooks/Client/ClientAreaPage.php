<?php

namespace RealtimeRegisterDomains\Hooks\Client;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;

class ClientAreaPage extends Hook
{
    public function __invoke(DataObject $vars)
    {
        // Skip in case there are no lookup.
        if (empty($vars['searchResults']) || empty($vars['searchResults']['domainName'])) {
            return;
        }

        dd($vars);
    }
}

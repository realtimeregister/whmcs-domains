<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\Entities\DataObject;

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

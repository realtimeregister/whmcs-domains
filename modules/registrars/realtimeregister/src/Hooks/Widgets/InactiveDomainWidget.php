<?php

namespace RealtimeRegisterDomains\Hooks\Widgets;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;

class InactiveDomainWidget extends Hook
{
    public function __invoke(DataObject $vars): \RealtimeRegisterDomains\Widget\InactiveDomainsWidget
    {
        return new \RealtimeRegisterDomains\Widget\InactiveDomainsWidget($vars);
    }
}

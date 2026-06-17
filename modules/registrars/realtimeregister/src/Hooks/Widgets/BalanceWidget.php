<?php

namespace RealtimeRegisterDomains\Hooks\Widgets;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;

class BalanceWidget extends Hook
{
    public function __invoke(DataObject $vars): \RealtimeRegisterDomains\Widget\BalanceModuleWidget
    {
        return new \RealtimeRegisterDomains\Widget\BalanceModuleWidget($vars);
    }
}

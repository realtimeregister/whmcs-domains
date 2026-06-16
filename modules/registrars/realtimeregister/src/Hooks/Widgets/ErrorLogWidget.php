<?php

namespace RealtimeRegisterDomains\Hooks\Widgets;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;

class ErrorLogWidget extends Hook
{
    public function __invoke(DataObject $vars): \RealtimeRegisterDomains\Widget\ErrorLogWidget
    {
        return new \RealtimeRegisterDomains\Widget\ErrorLogWidget($vars);
    }
}

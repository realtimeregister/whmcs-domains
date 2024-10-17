<?php

namespace RealtimeRegisterDomains\Hooks\Widgets;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;
use WHMCS\Module\AbstractWidget;

class UpdateWidget extends Hook
{
    public function __invoke(DataObject $vars): \RealtimeRegisterDomains\Widget\UpdateWidget
    {
        return new \RealtimeRegisterDomains\Widget\UpdateWidget();
    }
}

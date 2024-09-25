<?php

namespace RealtimeRegister\Hooks\Widgets;

use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Hooks\Hook;
use WHMCS\Module\AbstractWidget;

class UpdateWidget extends Hook
{
    public function __invoke(DataObject $vars): \RealtimeRegister\Widget\UpdateWidget
    {
        return new \RealtimeRegister\Widget\UpdateWidget();
    }
}

<?php

namespace RealtimeRegisterDomains\Hooks\Widgets;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;

class PromoWidget extends Hook
{
    public function __invoke(DataObject $vars)
    {
        return new \RealtimeRegisterDomains\Widget\PromoWidget();
    }
}

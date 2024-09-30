<?php

namespace RealtimeRegister\Hooks\Widgets;

use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Hooks\Hook;

class PromoWidget extends Hook
{
    public function __invoke(DataObject $vars)
    {
        return new \RealtimeRegister\Widget\PromoWidget();
    }
}

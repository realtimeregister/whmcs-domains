<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\PunyCode;

class OrderDomainPricingOverride extends Hook
{
    use PunyCode;

    public function __invoke(DataObject $vars)
    {
        if ($vars['type'] === 'register') {
            $res = App::client()->domains->check($vars['domain']);

            if ($res->price !== null) {
                return $res->price;
            }
        }
    }
}

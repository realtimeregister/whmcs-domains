<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\PunyCode;

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

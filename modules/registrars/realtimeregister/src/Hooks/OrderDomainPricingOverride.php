<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use TrueBV\Punycode;

class OrderDomainPricingOverride extends Hook
{

    public function __invoke(DataObject $vars)
    {
        $punyCode = new Punycode();

        if ($vars['type'] === 'register') {
            $res = App::client()->domains->check($punyCode->encode($vars['domain']));

            if ($res->price !== null) {
                return $res->price;
            }
        }
    }
}

<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Services\ShoppingCartService;

class ShoppingCartValidate extends Hook
{
    public function __invoke(DataObject $vars)
    {
        return ShoppingCartService::validateCartDomains();
    }
}

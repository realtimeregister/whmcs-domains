<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Services\ShoppingCartService;

class ShoppingCartValidate extends Hook
{
    public function __invoke(DataObject $vars)
    {
        return ShoppingCartService::validateCartDomains();
    }
}

<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Contracts\InvokableHook;

abstract class Hook implements InvokableHook
{
    public function __construct(protected App $app)
    {
    }
}

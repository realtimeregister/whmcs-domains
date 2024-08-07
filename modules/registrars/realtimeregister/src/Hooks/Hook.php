<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Contracts\InvokableHook;

abstract class Hook implements InvokableHook
{
    public function __construct(protected App $app)
    {
    }
}

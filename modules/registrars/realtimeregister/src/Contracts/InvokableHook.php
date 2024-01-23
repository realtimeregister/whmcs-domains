<?php

namespace RealtimeRegister\Contracts;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Request;

interface InvokableHook
{
    public function __construct(App $app);

    public function __invoke(DataObject $vars);
}
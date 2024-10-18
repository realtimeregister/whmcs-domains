<?php

namespace RealtimeRegisterDomains\Contracts;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;

interface InvokableHook
{
    public function __construct(App $app);

    public function __invoke(DataObject $vars);
}

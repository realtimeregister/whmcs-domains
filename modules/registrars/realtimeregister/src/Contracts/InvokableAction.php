<?php

namespace RealtimeRegisterDomains\Contracts;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;

interface InvokableAction
{
    public function __construct(App $app);

    public function __invoke(Request $request);
}

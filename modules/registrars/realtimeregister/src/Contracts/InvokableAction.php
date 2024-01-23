<?php

namespace RealtimeRegister\Contracts;

use RealtimeRegister\App;
use RealtimeRegister\Request;

interface InvokableAction
{
    public function __construct(App $app);

    public function __invoke(Request $request);
}
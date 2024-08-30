<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;

class AdminHomepage extends Hook
{
    public function __invoke(DataObject $vars): void
    {
        App::assets()->addScript("util.js");
        App::assets()->addStyle("general.css");
        App::assets()->addStyle("actions.css");
    }
}

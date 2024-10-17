<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;

class AdminHomepage extends Hook
{
    public function __invoke(DataObject $vars): void
    {
        App::assets()->addScript("util.js");
        App::assets()->addStyle("general.css");
        App::assets()->addStyle("actions.css");
    }
}

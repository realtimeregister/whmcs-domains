<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;

class AdminAreaFooterOutput extends Hook
{
    public function __invoke(DataObject $vars): string
    {
        return App::assets()->renderFooter();
    }
}

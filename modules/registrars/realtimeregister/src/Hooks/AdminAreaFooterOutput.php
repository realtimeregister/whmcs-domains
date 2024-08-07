<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;

class AdminAreaFooterOutput extends Hook
{
    public function __invoke(DataObject $vars): string
    {
        return App::assets()->renderFooter();
    }
}

<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Services\Assets;

class HeadAssets extends Hook
{
    public function __invoke(DataObject $vars)
    {
        return (new Assets())->renderHead();
    }
}

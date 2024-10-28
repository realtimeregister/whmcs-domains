<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;

class PreRegistrarGetContactDetails extends Hook
{
    public function __invoke(DataObject $vars)
    {
        App::assets()->addScript('rtrHandleMapping.js');
    }
}

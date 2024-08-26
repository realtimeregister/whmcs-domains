<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Services\Assets;

class HeadAssets extends Hook
{
    public function __invoke(DataObject $vars)
    {
        return (new Assets())->renderHead();
    }
}

<?php

namespace RealtimeRegister\Hooks\Update;

use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Hooks\Hook;
use RealtimeRegister\Services\UpdateService;

class CheckForUpdates extends Hook
{
    public function __invoke(DataObject $vars): void
    {
        $updateService = new UpdateService();
        $updateService->check();
    }
}

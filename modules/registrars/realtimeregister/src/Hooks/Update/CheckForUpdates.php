<?php

namespace RealtimeRegisterDomains\Hooks\Update;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;
use RealtimeRegisterDomains\Services\UpdateService;

class CheckForUpdates extends Hook
{
    public function __invoke(DataObject $vars): void
    {
        $updateService = new UpdateService();
        $updateService->check();
    }
}

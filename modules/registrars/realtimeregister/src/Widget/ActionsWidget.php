<?php

namespace RealtimeRegisterDomains\Widget;

use RealtimeRegisterDomains\Services\TemplateService;

class ActionsWidget extends BalanceModuleWidget
{
    protected $title = 'Realtime Register - Actions';
    protected $description = '';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = false;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';

    public function generateOutput($data): string
    {
        $status = 'online';
        if (is_numeric($data)) {
            $status = 'offline';
        }

        return TemplateService::renderTemplate("admin/widget/actions.tpl", ['status' => $status]);
    }
}

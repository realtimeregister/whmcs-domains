<?php

namespace RealtimeRegister\Widget;

use RealtimeRegister\Services\TemplateService;

class ToolsWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'Realtime Register - Tools';
    protected $description = '';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = false;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';

    public function getData(): array
    {
        return [];
    }

    public function generateOutput($data): string
    {
        return TemplateService::renderTemplate("actions.tpl");
    }
}

<?php

namespace RealtimeRegisterDomains\Widget;

use RealtimeRegisterDomains\Services\TemplateService;

class ErrorLogWidget extends BaseWidget
{
    protected $title = 'Realtime Register - Error Log';
    protected $description = '';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = false;

    public function getData(): array
    {
        return [];
    }

    public function generateOutput($data): ?string
    {
        return TemplateService::renderTemplate(
            'admin' . DIRECTORY_SEPARATOR . 'widget' . DIRECTORY_SEPARATOR . 'error_log.tpl'
        );
    }
}

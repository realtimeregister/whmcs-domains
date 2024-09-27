<?php

namespace RealtimeRegister\Widget;

use RealtimeRegister\Services\LogService;
use RealtimeRegister\Services\TemplateService;

class ErrorLogWidget extends \WHMCS\Module\AbstractWidget
{
    protected $title = 'Realtime Register - Error Log';
    protected $description = '';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = false;

    public function getData(): array
    {
        return LogService::getErrors();
    }

    public function generateOutput($data): ?string
    {
        return TemplateService::renderTemplate(
            'admin' . DIRECTORY_SEPARATOR . 'widget' . DIRECTORY_SEPARATOR . 'error_log.tpl',
            [
                "logs" => $data
            ]
        );
    }
}
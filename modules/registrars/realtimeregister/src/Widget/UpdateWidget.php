<?php

namespace RealtimeRegisterDomains\Widget;

use RealtimeRegisterDomains\Services\TemplateService;

class UpdateWidget extends BaseWidget
{
    protected $title = 'Realtimeregister updates';
    protected $wrapper = false;
    protected $description = '';
    protected $weight = -99;
    protected $columns = 3;
    protected $cache = true;
    protected $cacheExpiry = 18600;
    protected $requiredPermission = 'Main Homepage';

    public function getData(): array
    {
        return [
            'updates_available' =>
                !empty($GLOBALS['updates_available']['realtimeregister'])
                    ? $GLOBALS['updates_available']['realtimeregister'] : [],
        ];
    }

    public function generateOutput($data): ?string
    {
        if (!empty($data['updates_available'])) {
            return TemplateService::renderTemplate(
                'admin' . DIRECTORY_SEPARATOR . 'widget' . DIRECTORY_SEPARATOR . 'update-notification.tpl',
                [
                    'update_data' => $data['updates_available'][0],
                ]
            );
        }
        return null;
    }
}

<?php

namespace RealtimeRegisterDomains\Widget;

use Illuminate\Database\Capsule\Manager as Capsule;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Services\TemplateService;

class UpdateWidget extends BaseWidget
{
    protected $title = 'Realtimeregister updates';
    protected $wrapper = false;
    protected $description = '';
    protected $weight = -99;
    protected $columns = 3;
    protected $cache = true;
    protected $cacheExpiry = 10800;
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
            if (
                version_compare(
                    filter_var(App::VERSION, FILTER_SANITIZE_NUMBER_FLOAT),
                    filter_var($data['updates_available'][0]->version, FILTER_SANITIZE_NUMBER_FLOAT)
                ) >= 0
            ) {
                $this->deleteCurrentReference();
            } else {
                return TemplateService::renderTemplate(
                    'admin' . DIRECTORY_SEPARATOR . 'widget' . DIRECTORY_SEPARATOR . 'update-notification.tpl',
                    [
                        'update_data' => $data['updates_available'][0],
                    ]
                );
            }
        }
        return null;
    }

    public function deleteCurrentReference(): void
    {
        Capsule::table('tblregistrars')
            ->where('registrar', 'realtimeregister')
            ->where('setting', 'version_information')
            ->delete();
    }
}

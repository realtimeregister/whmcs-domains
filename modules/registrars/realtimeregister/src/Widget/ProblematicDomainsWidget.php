<?php

namespace RealtimeRegisterDomains\Widget;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\RealtimeRegister\ProblematicDomains;
use RealtimeRegisterDomains\Services\TemplateService;

class ProblematicDomainsWidget extends BaseWidget
{
    protected $title = 'Realtime Register - Problemantic Domains';
    protected $description = '';
    protected $weight = 150;
    protected $columns = 1;
    protected $cache = false;
    protected $cacheExpiry = 120;
    protected $requiredPermission = '';

    public function getData(): array
    {
        return ProblematicDomains::all()->toArray();
    }

    public function generateOutput($data): string
    {
        return TemplateService::renderTemplate(
            'admin' . DIRECTORY_SEPARATOR . 'widget' . DIRECTORY_SEPARATOR . 'problematic_domains.tpl',
            [
                'problems' => $data,
                'baseLink' => App::portalUrl() . '/app/domains/'
            ]
        );
    }
}

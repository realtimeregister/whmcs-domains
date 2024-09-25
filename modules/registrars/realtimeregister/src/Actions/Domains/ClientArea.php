<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Hooks\Hook;
use RealtimeRegister\Services\TemplateService;

class ClientArea extends Hook
{
    public function __invoke(DataObject $vars): bool|string
    {
        global $_LANG;

        return TemplateService::renderTemplate(
            'child_hosts_button.tpl',
            [
                "fields" => [
                    "domainid" => $vars->get('domainid'),
                    "LANG" => $_LANG
                ]
            ]
        );
    }
}

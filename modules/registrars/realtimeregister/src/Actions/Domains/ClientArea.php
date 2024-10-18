<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\TemplateService;

class ClientArea extends Action
{
    public function __invoke(Request $request): bool | string
    {
        global $_LANG;

        return TemplateService::renderTemplate(
            'child_hosts_button.tpl',
            [
                "fields" => [
                    "domainid" => $request->get('domainid'),
                    "LANG" => $_LANG
                ]
            ]
        );
    }
}

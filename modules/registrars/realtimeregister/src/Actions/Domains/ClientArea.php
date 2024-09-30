<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\Request;
use RealtimeRegister\Services\TemplateService;

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

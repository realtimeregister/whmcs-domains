<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use Smarty;

trait SmartyTrait
{
    private function render(string $template, ?array $parameters): bool|string
    {
        $smarty = new Smarty();

        global $_LANG;

        $parameters['LANG'] = $_LANG;
        $smarty->assign($parameters);

        return $smarty->fetch($template);
    }
}

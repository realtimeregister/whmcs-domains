<?php

namespace RealtimeRegister\Services;

use Smarty;

class TemplateService
{
    public static function renderTemplate(string $templatePath, array $args = []): false|string
    {
        global $_LANG;
        $smarty = new Smarty();

        $args['LANG'] = $_LANG;

        foreach ($args as $key => $value) {
            $smarty->assign($key, $value);
        }

        foreach ($args as $key => $value) {
            $smarty->assign($key . 'JSON', json_encode($value));
        }

        return $smarty->fetch(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Assets', 'Tpl', $templatePath]));
    }
}

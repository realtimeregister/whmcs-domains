<?php

namespace RealtimeRegister {
    if (!function_exists('view')) {
        /**
         * @param  $template
         * @param  array $args
         * @return string
         * @throws Exception
         * @throws SmartyException
         */
        function view($template, array $args = []): string
        {
            $templatePath = __DIR__ . ltrim($template, '/') . '.tpl';

            $smarty = new Smarty();

            if (!empty($args)) {
                foreach ($args as $key => $value) {
                    $smarty->assign($key, $value);
                }
            }

            return $smarty->fetch($templatePath);
        }
    }
}

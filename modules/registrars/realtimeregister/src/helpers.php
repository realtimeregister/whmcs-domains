<?php

namespace RealtimeRegister {

    use RealtimeRegister\Enums\WhmcsDomainStatus;
    use SandwaveIo\RealtimeRegister\Domain\Enum\DomainStatusEnum;

    if (!function_exists('view')) {
        /**
         * @param $template
         * @param array $args
         * @return string
         * @throws Exception
         * @throws SmartyException
         */
        function view($template, array $args = []): string
        {
            $templatePath = __DIR__ . ltrim($template, '/'). '.tpl';

            $smarty = new Smarty;

            if (!empty($args)) {
                foreach ($args as $key => $value) {
                    $smarty->assign($key, $value);
                }
            }

            return $smarty->fetch($templatePath);
        }
    }

    if (!function_exists('dd')) {
        function dd(mixed ...$vars): never
        {
            if (!in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) && !headers_sent()) {
                header('HTTP/1.1 500 Internal Server Error');
            }

            dump(...$vars);

            exit(1);
        }
    }

    if (!function_exists('dump')) {
        function dump(mixed ...$vars): void
        {
            if (array_key_exists(0, $vars) && 1 === count($vars)) {
                echo '<pre style="background-color: black; color: white; padding: 3px;">';
                var_dump($vars[0]);
                echo '</pre>';
            } else {
                foreach ($vars as $k => $v) {
                    echo '<pre style="background-color: black; color: white; padding: 3px;">';
                    var_dump($v, is_int($k) ? 1 + $k : $k);
                    echo '</pre>';
                }
            }
        }
    }
}
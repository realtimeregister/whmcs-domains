<?php

namespace RealtimeRegister\Logger;

use Psr\Log\AbstractLogger;
use RealtimeRegister\App;
use RealtimeRegister\Services\MailService;

class DebugMailLogger extends AbstractLogger
{
    public function log($level, $message, array $context = array()): void
    {
        $subject = sprintf('%s: %s', $context['method'], $context['endpoint']);

        $message = '<div style="padding: 5px;">' . PHP_EOL;
        $message .= '<p>Date/Time: ' . date('d-m-Y H:i:s') . '</p>' . PHP_EOL;

        $message .= 'METHOD: ' . $context['method'] . '<br>' . PHP_EOL;
        $message .= 'URL: ' . $context['endpoint'] . '<br>' . PHP_EOL;

        $postDataArray = (array)$context['meta_data'];
        if (!empty($postDataArray)) {
            $message .= '<br>' . PHP_EOL . 'POST Data:<br>' . PHP_EOL;
            $message .= '<pre>' . htmlentities(var_export($postDataArray, true)) . '</pre>' . PHP_EOL;
        }

        if ($context['response_body']) {
            $message .= 'HTTP Status: ' . $context['response_code'] . '<br>' . PHP_EOL;
            $message .= '<br>' . PHP_EOL . 'HEADER: <br>' . PHP_EOL . '<pre>';
            foreach ($context['headers'] as $header) {
                $message .= htmlentities(implode(' ', $header)) . '<br>';
            }
            $message .= '</pre><br>' . PHP_EOL;
            $message .= '<br>' . PHP_EOL . 'BODY: <br>' . PHP_EOL . '<pre>' . htmlentities(
                var_export(json_decode($context['response_body'], 1), true)
            ) . '</pre><br>' . PHP_EOL;
        }
        $message .= '</div>' . PHP_EOL;

        // Check if debug mode on and we're allowed to record error (means
        // error has not occurred yet).
        if (App::registrarConfig()->get('debug_mode') !== null) {
            logModuleCall(
                App::NAME,
                $context['method'],
                is_array($context['body']) ? implode(PHP_EOL, $context['body']) : $context['body'],
                is_array($context['response_body'])
                    ? implode(PHP_EOL, $context['response_body']) : $context['response_body'],
            );
        }

        if (filter_var(App::registrarConfig()->get('debug_mail'), FILTER_VALIDATE_EMAIL)) {
            MailService::mail(App::registrarConfig()->get('debug_mail'), $subject, $message);
        }
    }
}

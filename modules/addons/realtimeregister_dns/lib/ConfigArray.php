<?php

namespace WHMCS\Module\Addon\RealtimeregisterDns;

class ConfigArray
{
    public function __invoke(): array
    {
        return [
            'name' => 'Realtime Register Dns Module',
            'description' => 'This module provider DNS support for Realtime Register DNS.',
            'author' => 'Realtime Register BV',
            'language' => 'english',
            'version' => '0.1',
            'fields' => [
                'active' => [
                    'FriendlyName' => 'Allow DNS management',
                    'Type' => 'yesno',
                    'Description' => 'Tick to enable',
                ],
            ]
        ];
    }
}

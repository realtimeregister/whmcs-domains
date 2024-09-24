<?php

namespace RealtimeRegister;

class ConfigArray
{
    public function __invoke(): array
    {
        return [
            'FriendlyName' => [
                'Type'  => 'System',
                'Value' => 'Realtime Register'
            ],
            'Description' => [
                'Type'  => 'System',
                'Value' => 'The Domains &amp; Digital Security Experts'
            ],
            /**
             * Account information.
             */
            'INFO_ACCOUNT' => [
                'FriendlyName' => '<strong>Realtime Register Account</strong>',
                'Type' => 'none',
                'Description' => '<strong>Please fill in your Realtime Register credentials.</strong>',
            ],
            'customer_handle' => [
                'FriendlyName' => 'Customer handle',
                'Type' => 'text',
                'Size' => '20',
                'Description' => 'Enter your Realtime Register customer handle here.',
            ],
            'rtr_api_key' => [
                'FriendlyName' => 'API Key',
                'Type' => 'password',
                'Size' => '20',
                'Description' => 'Enter your Realtime Register <a target="_blank" ' .
                    'href="https://dm.realtimeregister.com/app/profile/keys">API key</a> here.',
            ],
            'check_credentials' => [
                'FriendlyName' => "<br/>",
                'Type' => 'none',
                'Description' => '<button class="btn btn-xs check-connection">Check Connection</button>
<span class="credentials-result"></span>',
            ],
            /**
             * General settings.
             */
            'INFO_GENERAL' => [
                'FriendlyName' => '<strong>General settings</strong>',
                'Type' => 'none',
                'Description' => '<strong>General settings for management of your WHMCS module.</strong>',
            ],
            'brand' => [
                'FriendlyName' => 'Brand',
                'Type' => 'text',
                'Default' => 'default',
                'Size' => '20',
                'Description' => 'Specify the brand that will be used when creating new contacts.',
            ],
            'contact_handle_prefix' => [
                'FriendlyName' => 'Contact handle prefix',
                'Type' => 'text',
                'Default' => 'srs_',
                'Size' => '20',
                'Description' => 'The prefix used for creating new contact handles.',
            ],
            'transfer_lock' => [
                'FriendlyName' => 'Transfer Lock',
                'Type' => 'yesno',
                'Description' => 'Enable Transfer Lock by default for all supported extensions. <i>(new domains)</i>',
            ],
            'transfer_keep_nameservers' => [
                'FriendlyName' => 'Keep nameservers for transfers',
                'Type' => 'yesno',
                'Description' => 'Enable this ONLY when you want transfers to keep their existing nameservers.',
            ],
            'dnssec' => [
                'FriendlyName' => 'DNSSec',
                'Type' => 'yesno',
                'Description' => 'Enable DNSSec management for all supported extensions.',
            ],
            'test_mode' => [
                'FriendlyName' => 'Test mode',
                'Type' => 'yesno',
                'Description' => 'Use the test environment, request a test account at ' .
                    '<a href="mailto:support@realtimeregister.com">support@realtimeregister.com</a>',
            ],
            'ignore_ssl' => [
                'FriendlyName' => 'Ignore SSL errors',
                'Type' => 'yesno',
                'Description' => 'Enable this ONLY when you are having connectivity issues with ' .
                    'Realtime Register due to SSL errors.',
            ],
            'debug_mode' => [
                'FriendlyName' => 'Debug errors',
                'Type' => 'yesno',
                'Description' => 'Debug mode for extensive information when you encounter errors. ' .
                    'Activate for troubleshooting.',
            ],
            'debug_mail' => [
                'FriendlyName' => 'Debug API requests',
                'Type' => 'text',
                'Description' => 'Specificy your email address to which you want to receive debug information. ' .
                    '<br/> If you do not want to receive debug information, leave this field empty.',
            ],
            'INFO_GENERAL_EMPTY_LINE' => [
                'FriendlyName' => '</br>',
                'Type' => 'none',
            ],
            /**
             * Registration Profile.
             */
            'INFO_REGISTRATION' => [
                'FriendlyName' => '<strong>Registration Profile</strong>',
                'Type' => 'none',
                'Description' => '<strong>With your registration profile you can set default handles for Admin, ' .
                    'Billing and Tech contact which will be used for domain registrations and transfers.<br/>
If you do not use this option, the registrant details will be used for Admin, Billing and Tech.</strong>',
            ],
            'handle' => [
                'FriendlyName' => 'Registration profile admin handle',
                'Type' => 'text',
                'Size' => '20',
                'Description' => 'Optionally override the default contact handle for Admin.',
            ],
            'handle_billing' => [
                'FriendlyName' => 'Registration profile billing handle',
                'Type' => 'text',
                'Size' => '20',
                'Description' => 'Optionally override the default contact handle for Billing.',
            ],
            'handle_tech' => [
                'FriendlyName' => 'Registration profile tech handle',
                'Type' => 'text',
                'Size' => '20',
                'Description' => 'Optionally override the default contact handle for Tech.',
            ],
            'INFO_REGISTRATION_EMPTY_LINE' => [
                'FriendlyName' => '</br>',
                'Type' => 'none',
            ],

            /**
             * Adac
             */
            'INFO_ADAC' => [
                'FriendlyName' => '<strong>ADAC</strong>',
                'Type' => 'none',
                'Description' => '<strong>Use Realtime Register ADAC domain check instead of WHMCS for a more quick ' .
                    'and better result.</strong>',
            ],
            'adac_token' => [
                'FriendlyName' => 'TLD set token',
                'Type' => 'text',
                'Size' => '20',
                'Description' => '',
            ],
            'adac_key' => [
                'FriendlyName' => 'API key',
                'Type' => 'password',
                'Size' => '20',
                'Description' => '',
            ],

            /**
             * Domain availability check.
             */
            'INFO_WHOIS' => [
                'FriendlyName' => '<strong>Domain availability check</strong>',
                'Type' => 'none',
                'Description' => '<strong>Use Realtime Register WHOIS check instead of WHMCS for a more quick and ' .
                    'better result.</strong>',
            ],
            'rtrWhois' => [
                'FriendlyName' => 'Use Realtimeregister WHOIS',
                'Type' => 'yesno',
                'Description' =>
                    'Adds WHOIS layer over WHMCS core to check domain availability. ' .
                    'See installation guide for instructions.',
            ],
            'INFO_WHOIS_EMPTY_LINE' => [
                'FriendlyName' => '</br>',
                'Type' => 'none',
            ],
            // This next value is needed, but hidden by checkCredentials.js
            'customHandles' => [
                'FriendlyName' => 'Custom handles',
                'Type' => 'text',
            ]
        ];
    }
}

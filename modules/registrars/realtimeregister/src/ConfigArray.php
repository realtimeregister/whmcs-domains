<?php

namespace RealtimeRegisterDomains;

use Illuminate\Database\Capsule\Manager as Capsule;
use RealtimeRegister\Domain\Enum\ZoneServiceEnum;

class ConfigArray
{
    public function __invoke(): array
    {
        $configArray = [];

        $result = Capsule::table("tbladdonmodules")->where('module', 'realtimeregister_tools')->first();

        if ($result) {
            $configArray[] = [
                'FriendlyName' => '<div class="label label-danger">Realtime Register tools detected</div>',
                'Type' => 'none',
                'Description' => '<h1 class="text-danger"> 
                        <i class="fas fa-exclamation-triangle"></i> 
                        Please remove Realtime Register tools before continuing 
                        <i class="fas fa-exclamation-triangle"></i>
                    </h1>',
            ];
        }

        return array_merge(
            $configArray,
            [
                'FriendlyName' => [
                    'Type' => 'System',
                    'Value' => 'Realtime Register'
                ],
                'Description' => [
                    'Type' => 'System',
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
                    'Description' => '<button type="button" class="btn btn-xs check-connection">Check Connection' .
                        '</button><span class="credentials-result"></span>',
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
                    'Description' =>
                        'Enable Transfer Lock by default for all supported extensions. <i>(new domains)</i>',
                ],
                'transfer_keep_nameservers' => [
                    'FriendlyName' => 'Keep nameservers for transfers',
                    'Type' => 'yesno',
                    'Description' => 'Enable this ONLY when you want transfers to keep their existing nameservers.',
                ],
                'required_additional_fields' => [
                    'FriendlyName' => 'Required additional fields',
                    'Type' => 'yesno',
                    'Description' => 'The order can only be completed by the customer when he/she completes the ' .
                        'required fields as well. If this option is enabled, the client must fill in the fields that ' .
                        'are usually required. If this option is disabled, any missing required fields have to be ' .
                        'set by the admin.',
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
                'debug_mode' => [
                    'FriendlyName' => 'Debug errors',
                    'Type' => 'yesno',
                    'Description' => 'Debug mode for extensive information when you encounter errors. ' .
                        'Activate for troubleshooting.',
                ],
                'debug_mail' => [
                    'FriendlyName' => 'Debug API requests',
                    'Type' => 'text',
                    'Description' => 'Specify your email address to which you want to receive debug information. ' .
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
                'contact_handles' => [
                    'FriendlyName' => "Registry specific default Admin, Billing and Tech contacts",
                    'Type' => 'none',
                    'Description' => '<button class="btn btn-xs info rtr-custom-properties" type="button" 
                            data-toggle="modal" data-target="#propertiesModal">Contact Handles Override
                        </button>',
                ],
                'dns_support' => [
                    'FriendlyName' => 'DNS support',
                    'Type' => 'dropdown',
                    'Options' => [
                        'none',
                        ZoneServiceEnum::BASIC->value => ZoneServiceEnum::BASIC->value,
                        ZoneServiceEnum::PREMIUM->value => ZoneServiceEnum::PREMIUM->value,
                    ],
                    'Default' => 'none',
                    'Description' => 'Enable DNS support from Realtime Register. The option <strong>Premium</strong> ' .
                        'will yield <b>1 paid zone per domain</b>. The price of the DNS support can be set in the ' .
                        'domainpricing overview in WHMCS, individual TLDs can be enabled and disabled there as well. ' .
                        'As indicated in our Terms and conditions; BASIC DNS can only be offered for free'
                ],
                'dns_vanity_nameservers' => [
                    'FriendlyName' => 'DNS Vanity Nameservers',
                    'Type' => 'none',
                    'Description' => 'Override the default DNS nameservers to your own vanity nameservers <a href=' .
                        '"https://kb.realtimeregister.com/article/372-setup-vanity-nameservers-for-premium-dns-zones"' .
                        ' target="_blank">More information</a>',
                ],
                'dns_vanity_nameserver_1' => [
                    'FriendlyName' => 'DNS Vanity Nameserver 1',
                    'Type' => 'text',
                    'Description' => '<br>Only fill this out, when you have setup your own vanity nameservers, ' .
                        'by default, we use the default nameservers provided by Realtime Register'
                ],
                'dns_vanity_nameserver_2' => [
                    'FriendlyName' => 'DNS Vanity Nameserver 2',
                    'Type' => 'text',
                    'Description' => '<br>Only fill this out, when you have setup your own vanity nameservers, ' .
                        'by default, we use the default nameservers provided by Realtime Register'
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
                    'Description' => '<strong>Use Realtime Register ADAC domain check instead of WHMCS for a more ' .
                        'quick and better result.</strong>',
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
                'INFO_WHOIS_EMPTY_LINE' => [
                    'FriendlyName' => '</br>',
                    'Type' => 'none',
                ],
            ]
        );
    }
}

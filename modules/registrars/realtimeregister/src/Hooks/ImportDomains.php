<?php

namespace RealtimeRegister\Hooks;

use JetBrains\PhpStorm\NoReturn;
use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Models\RealtimeRegister\Cache;
use RealtimeRegister\Models\Whmcs\AdditionalFields;
use RealtimeRegister\Models\Whmcs\Admin;
use RealtimeRegister\Models\Whmcs\Client;
use RealtimeRegister\Models\Whmcs\Configuration;
use RealtimeRegister\Models\Whmcs\Domain;
use RealtimeRegister\Models\Whmcs\DomainPricing;
use RealtimeRegister\Models\Whmcs\PaymentGateway;
use RealtimeRegister\Services\Config\Config;
use RealtimeRegister\Services\MetadataService;
use RealtimeRegister\Services\TemplateService;

class ImportDomains extends Hook
{
    private string $ACTION = 'importWizard';

    public function __invoke(DataObject $vars): void
    {
        if ($_REQUEST['action'] === $this->ACTION && $_REQUEST['module'] == 'realtimeregister') {
            self::importWizard();
        }
    }

    #[NoReturn] private static function importWizard(): void
    {
        switch ($_POST['step']) {
            case 2:
                self::step2();
                break;
            case 3:
                self::step3();
                break;
            default:
                self::step1();
                break;
        }
        exit;
    }

    private static function step1(): void
    {
        $brands = App::client()->brands->export(
            App::registrarConfig()->customerHandle(),
            ["fields" => "organization,handle,email"]
        );

        $domains = array_map(
            fn($domain) => $domain['domainName'],
            App::client()->domains->export(["fields" => "domainName"])
        );
        $paymentGateways = PaymentGateway::query()
            ->select('gateway', 'value')
            ->where('setting', '=', 'name')
            ->get()
            ->toArray();

        $fields = $_POST['fields'] ?? [];

        echo TemplateService::renderTemplate(
            'importDomains.tpl',
            [
                "fields" => array_merge(
                    ["brandSelectionList" => [],
                    "allBrands" => $brands,
                    "allDomains" => $domains,
                    "gateways" => $paymentGateways,
                    "nonActiveTlds" => self::getNonActiveTlds($domains),
                    'domainSelectionMethod' => 'all',
                    "brandSelectionMethod" => "contactsAsClients"],
                    $fields
                )
            ]
        );
    }

    private static function step2(): void
    {
        echo TemplateService::renderTemplate(
            'importDomainsStepTwo.tpl',
            [
            "fields" => $_POST['fields'],
            ]
        );
    }



    private static function getDomainName(string $domain)
    {
        if (Config::get('tldinfomapping.' . MetadataService::getTld($domain)) === 'centralnic') {
            return $domain . '.centralnic';
        }
        return $domain;
    }

    public static function mergeRegistrantContactInformation($domains): array
    {
        $customer = App::registrarConfig()->customerHandle();

        foreach ($domains as &$domain) {
            $domain['registrant'] = Cache::remember(
                "contact." . $customer . "." . $domain['registrant'],
                30,
                function () use ($customer, $domain) {
                    return App::client()->contacts->get($customer, $domain['registrant'])->toArray();
                }
            );
            $domain['brand'] = App::client()->brands->get($customer, $domain['registrant']['brand'])->toArray();
        }

        return $domains;
    }

    private static function step3(): void
    {
        if ($_POST['domains']) {
            echo json_encode(["updated" => self::importDomains()]);
        } else {
            echo TemplateService::renderTemplate("importDomainsStepThree.tpl", ["fields" => $_POST['fields']]);
        }
    }

    private static function importDomains(): int
    {
        $domainNames = array_map(fn($domainName) => self::getDomainName($domainName), $_POST['domains']);
        $args = [
            'fields' => 'domainName,autoRenewPeriod,status,createdDate,expiryDate,registrant,ns,contacts,customer',
            'domainName:in' => implode(",", $domainNames)
        ];

        $domains = self::mergeRegistrantContactInformation(App::client()->domains->export($args));
        $brands = $_POST['fields']['selectedBrands'] ?? [];
        $paymentMethod = $_POST['fields']['paymentMethod'];
        $tldPricingCurrencyid = 1;
        $adminUser = self::getAdminUsername();
        $tldPricing = localAPI('GetTLDPricing', ['currencyid' => $tldPricingCurrencyid], $adminUser);

        $updated = 0;

        foreach ($domains as $domain) {
            if (Domain::exists($domain['domainName'])) {
                continue;
            }

            $userId = App::contacts()->fetchMappingByHandle($domain['registrant']['handle'])?->userid;
            if (!$userId) {
                if (in_array($domain['brand']['handle'], $brands)) {
                    $userId = self::createClient($domain['brand'], $adminUser);
                } else {
                    $userId = self::createClient($domain['registrant'], $adminUser);
                }

                $contactId = self::createContact($userId, $domain['registrant']);
                App::contacts()->addContactMapping($userId, $contactId, $domain['registrant']['handle'], true);
            }


            $metadata = new MetadataService($domain['domainName']);
            $expiryDate = $metadata->getOffsetExpiryDate($domain['expiryDate']);
            $dueDate = self::getSyncDueDate($expiryDate);

            $tld = MetadataService::getTld($domain['domainName']);
            $recurringAmount = '0.00';
            if (!empty($tldPricing['pricing'][$tld]['renew'][$tldPricingCurrencyid])) {
                $recurringAmount = $tldPricing['pricing'][$tld]['renew'][$tldPricingCurrencyid];
            }

            $domainId = Domain::query()->insertGetId(
                [
                'userid'             => $userId,
                'registrationdate'   => $domain['createdDate'],
                'domain'             => $domain['domainName'],
                'recurringamount'    => $recurringAmount,
                'registrar'          => 'realtimeregister',
                'registrationperiod' => ceil($domain['autoRenewPeriod'] / 12),
                'paymentmethod'      => $paymentMethod,
                'status'             => 'Active',
                //'is_premium'         => 0,
                'nextduedate'        => $dueDate,
                'nextinvoicedate'    => $dueDate,
                'expirydate'         => $expiryDate
                ]
            );

            $provider = $metadata->getProvider();

            if (!empty($domain['registrant']['properties'] && !empty($domain['registrant']['properties'][$provider]))) {
                foreach ($domain['registrant']['properties'][$provider] as $name => $value) {
                    AdditionalFields::query()->insert(
                        [
                        'domainid' => $domainId,
                        'name' => $name,
                        'value' => $value
                        ]
                    );
                }
            }

            $updated++;
        }
        return $updated;
    }

    private static function getAdminUsername()
    {
        return Admin::query()
            ->select(['username'])
            ->where('roleid', '=', 1)
            ->get()
            ->first()
            ->username;
    }

    public static function createClient($info, $admin)
    {
        $clientByEmail = Client::query()
            ->select(['id'])
            ->where('email', '=', $info['email'])
            ->first()
            ?->id;

        if ($clientByEmail) {
            return $clientByEmail;
        }

        $firstname = $lastname = false;
        if ($info['name']) {
            $name = explode(" ", $info['name']);
            $firstname = $name[0];
            unset($name[0]);
            $lastname = implode(" ", $name);
        }

        $postData = [
            'firstname'   => !empty($firstname) ? $firstname : 'unknown',
            'lastname'    => !empty($lastname) ? $lastname : 'unknown',
            'companyname' => $info['organization'] ?? '',
            'email'       => $info['email'] ?? '',
            'address1'    => !empty($info['addressLine'][0]) ? $info['addressLine'][0] : '',
            'address2'    => !empty($info['addressLine'][1]) ? $info['addressLine'][1] : '',
            'city'        => $info['city'] ?? '',
            'state'       => $info['state'] ?? 'n/a',
            'postcode'    => $info['postalCode'] ?? '',
            'country'     => $info['country'] ?? '',
            'phonenumber' => $info['voice'],
            'password2'   => password_hash(self::randomPassword(), PASSWORD_BCRYPT),
        ];

        $results = localAPI('AddClient', $postData, $admin);
        if ($results['result'] == 'success') {
            return $results['clientid'];
        } else {
            logActivity("Error for creating a client. An Error Occurred: " . implode(" | ", $results));
        }

        return 0;
    }

    public static function createContact($clientId, $info)
    {

        $firstname = $lastname = false;
        if ($info['name']) {
            $name = explode(" ", $info['name']);
            $firstname = $name[0];
            unset($name[0]);
            $lastname = implode(" ", $name);
        }

        $postData = [
            'clientid'    => $clientId,
            'firstname'   => !empty($firstname) ? $firstname : 'unknown',
            'lastname'    => !empty($lastname) ? $lastname : 'unknown',
            'companyname' => $info['organization'] ?? '',
            'email'       => $info['email'] ?? '',
            'address1'    => !empty($info['addressLine'][0]) ? $info['addressLine'][0] : '',
            'address2'    => !empty($info['addressLine'][1]) ? $info['addressLine'][1] : '',
            'city'        => $info['city'] ?? '',
            'state'       => $info['state'] ?? 'n/a',
            'postcode'    => $info['postalCode'] ?? '',
            'country'     => $info['country'] ?? '',
            'phonenumber' => $info['voice']
        ];

        $results = localAPI('AddContact', $postData, self::getAdminUsername());
        if ($results['result'] == 'success') {
            return $results['contactid'];
        } else {
            logActivity("Error for creating a contact. An Error Occurred: " . implode(" | ", $results));
        }

        return 0;
    }

    public static function getSyncDueDate($date)
    {
        $hasOffset =  Configuration::query()
            ->select(["value"])
            ->where("setting", '=', 'DomainSyncNextDueDate')
            ->first();
        $syncOffset = Configuration::query()
            ->select(["value"])
            ->where("setting", '=', 'DomainSyncNextDueDateDays')
            ->first();

        if ($hasOffset?->value && $syncOffset?->value) {
            return date("Y-m-d", strtotime($date . (-$syncOffset->value) . ' days'));
        }

        return $date;
    }

    private static function getNonActiveTlds(array $domains): array
    {
        $tlds = array_map(fn($domain) => MetadataService::getTld($domain), $domains);
        $activeTlds = array_map(
            fn($pricing) => $pricing['extension'],
            DomainPricing::query()->get(['extension'])->toArray()
        );
        return array_values(
            array_filter(
                $tlds,
                function ($tld) use ($activeTlds) {
                    return !in_array("." . strtolower($tld), $activeTlds);
                }
            )
        );
    }

    private static function randomPassword(): string
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";

        return substr(str_shuffle($chars), 0, 16);
    }
}

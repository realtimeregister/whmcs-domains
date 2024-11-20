<?php

namespace RealtimeRegisterDomains\Services;

use RealtimeRegister\Domain\TLDInfo;
use RealtimeRegister\Domain\TLDMetaData;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\RealtimeRegister\Cache;
use RealtimeRegisterDomains\Models\Whmcs\DomainPricing;
use RealtimeRegisterDomains\Services\Config\Config;

class MetadataService
{
    public const DAY_MINUTES = 14400;
    private string $tld;
    private string $provider;
    /**
     * @var TLDInfo
     */
    private $info;

    public function __construct(string $tld)
    {
        $tld = self::getTld($tld);

        $this->tld = $tld;
        $this->info = TLDInfo::fromArray(
            Cache::remember(
                'tld.' . $this->tld,
                MetadataService::DAY_MINUTES,
                function () {
                    $queryTld = explode('.', $this->tld);
                    $queryTld = array_pop($queryTld);
                    $metadata = App::client()->tlds->info(App::toPunyCode($queryTld));
                    foreach ($metadata->applicableFor as $app_tld) {
                        Cache::put('tld.' . $app_tld, $metadata->toArray(), MetadataService::DAY_MINUTES);
                    }
                    return $metadata->toArray();
                }
            )
        );

        $this->provider = $this->info->provider;
    }

    public function getMetadata(): TLDMetaData
    {
        return $this->info->metadata;
    }

    public function getAll(): TLDInfo
    {
        return $this->info;
    }

    /**
     * @param  string $param
     * @return string|int|array|bool
     */
    public function get(string $param)
    {
        $metadata = $this->info->metadata;
        if (!$metadata) {
            return [];
        }
        if (isset($this->info->metadata->toArray()[$param])) {
            return $this->info->metadata->toArray()[$param];
        } elseif (isset($this->info->toArray()[$param])) {
            return $this->info->toArray()[$param];
        }

        return [];
    }

    public function getApplicableFor()
    {
        return $this->info->applicableFor;
    }

    /**
     * @param  string $domain the domain name
     * @return string
     */
    public static function getTld($domain)
    {
        if (Config::get('tldinfomapping.' . $domain)) {
            return $domain;
        }

        $domain_parts = explode(".", $domain);
        if (count($domain_parts) >= 2) {
            unset($domain_parts[0]);
            return implode(".", $domain_parts);
        }

        return end($domain_parts);
    }

    public function getTldAdditionalFields(?string $defaultLanguageCode): array
    {
        global $_LANG;
        if (!self::isRtr($this->tld)) {
            return [];
        }
        $languageCodes = $this->get('domainSyntax')['languageCodes'];

        $tldAdditionalFields = [];

        if (!empty($languageCodes)) {
            $entry = [
                'Name' => 'languageCode',
                'LangVar' => 'rtr_languagecode_label',
                'Description' => $_LANG['rtr_languagecode_description'],
                'Type' => 'dropdown',
                'Options' => ',' . implode(',', array_keys($languageCodes)),
            ];
            if ($defaultLanguageCode) {
                $entry['Required'] = true;
                foreach (array_keys($languageCodes) as $languageCode) {
                    if (strtolower($languageCode) == $defaultLanguageCode) {
                        $entry['Default'] = $languageCode;
                        break;
                    }
                }
            }
            $tldAdditionalFields[] = $entry;
        }

        $properties = $this->get('contactProperties');

        $current = $this->parseCurrentProperties();

        if ($properties) {
            foreach ($properties as $property) {
                $default = array_key_exists($property['name'], $current) ? $current[$property['name']] : null;
                $tldAdditionalFields[] = $this->propertyToAdditionalField($property, $default);
            }
        }
        return ['fields' => $tldAdditionalFields, 'applicableFor' => $this->info->applicableFor];
    }

    private function parseCurrentProperties(): array
    {
        if (!$_SESSION['uid']) {
            return [];
        }

        $organizationAllowed = $this->get('registrant')['organizationAllowed'];
        $handle = ContactService::getContactMapping($_SESSION['uid'], 0, $organizationAllowed)?->handle;
        if (!$handle) {
            return [];
        }

        try {
            $rtr_contact = App::client()->contacts->get(App::registrarConfig()->customerHandle(), $handle);
        } catch (\Exception $e) {
            return [];
        }

        return $rtr_contact->properties[$this->provider] ?? [];
    }


    public static function toLangVar(string $tld, string $property_name): string
    {
        return sprintf('tld_%s_%s', strtolower($tld), preg_replace('/[^a-z0-9]/', '', strtolower($property_name)));
    }

    private function propertyToAdditionalField($property, $default = null): array
    {
        global $_LANG;
        $langvar = self::toLangVar($this->tld, $property['name']);
        $field = [
            'Name' => $property['name'],
            'LangVar' => $langvar . '_label',
            'Description' => $_LANG[$langvar . '_description']
        ];

        if (!empty($property['mandatory'])) {
            $field['Required'] = true;
        }

        if (!empty($property['values'])) {
            if (self::isBool($property['values'])) {
                $field['Type'] = 'tickbox';
            } else {
                $field['Type'] = 'dropdown';
                $field['Options'] = $this->arrayToOptions($property['values'], $property['name']);
            }
        } else {
            $field['Type'] = 'text';
        }

        if ($default) {
            $field['Default'] = $default;
        }

        return $field;
    }

    public static function isBool($propertyValues): bool
    {
        if (count($propertyValues) == 1 && array_keys($propertyValues)[0] == 'true') {
            return true;
        }
        if (count($propertyValues) == 2) {
            return count(array_intersect(['true', 'false', 'n', 'y'], array_keys($propertyValues))) == 2;
        }
        return false;
    }

    public static function getPropertyBoolValue($propertyValues, $bool)
    {
        return array_intersect($bool ? ['true', 'y'] : ['false', 'n'], array_keys($propertyValues))[0];
    }

    private function arrayToOptions(array $propertyValues, string $propertyName): string
    {
        global $_LANG;

        $options = [];
        foreach ($propertyValues as $key => $value) {
            $translationKey = sprintf(
                'tld_%s_%s_%s',
                strtolower($this->tld),
                preg_replace('/[^a-z0-9_]/', '', strtolower($propertyName)),
                preg_replace('/[^a-z0-9_]/', '', strtolower($key))
            );

            $options[] = self::strip($key) . '|' . self::strip($_LANG[$translationKey]);
        }
        return ',' . implode(',', $options);
    }

    private static function strip($str): array|string
    {
        return str_replace([',', '|'], ' ', $str);
    }

    public static function removeDefaultFields(&$rtrAdditionalFields): void
    {
        $defaultFields = implode(DIRECTORY_SEPARATOR, [ROOTDIR, 'resources', 'domains', 'dist.additionalfields.php']);

        if (!file_exists($defaultFields)) {
            return;
        }

        $additionaldomainfields = [];
        include $defaultFields;

        $tldNames = array_reduce(
            array_keys($rtrAdditionalFields),
            function ($names, $tld) use ($rtrAdditionalFields) {
                $names[$tld] = array_map(fn($field) => $field['Name'], $rtrAdditionalFields[$tld]);
                return $names;
            },
            []
        );

        foreach ($additionaldomainfields as $tld => $tld_fields) {
            foreach ($tld_fields as $tld_field) {
                if (!array_key_exists($tld, $tldNames)) {
                    continue;
                }
                if (!in_array($tld_field['Name'], $tldNames[$tld])) {
                    $rtrAdditionalFields[$tld][] = [
                        "Name" => is_array($tld_field) ? $tld_field["Name"] : $tld_field, "Remove" => true
                    ];
                }
            }
        }
    }

    public static function getAllTlds(): array
    {
        $providers = Cache::remember(
            "rtrProviders",
            self::DAY_MINUTES,
            fn () => App::client()->providers->export(parameters: ["fields" => "tlds"])
        );
        return array_map(
            fn($tld) => $tld['name'],
            array_merge(...array_map(fn($provider) => $provider['tlds'], $providers))
        );
    }

    public static function isRtr($tld): bool
    {
        return DomainPricing::query()
            ->where("extension", "." . $tld)
            ->whereIn("autoreg", ["realtimeregister", ""])
            ->first() !== null;
    }

    public function getOffsetExpiryDate(string $expiryDate): string
    {
        $offset = $this->get("expiryDateOffset") ?? 0;

        return date("Y-m-d", strtotime($expiryDate . " - " . ((int)$offset) . " seconds"));
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public static function getBulkData(): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, App::metadataUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Realtime Register WHMCS Client/' . App::VERSION);
        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);

        return $response['hashes'];
    }
}

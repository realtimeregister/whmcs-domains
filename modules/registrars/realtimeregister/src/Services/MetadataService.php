<?php

namespace RealtimeRegister\Services;

use Http\Discovery\Exception\NotFoundException;
use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Models\Cache;
use RealtimeRegister\Services\Config\Config;
use SandwaveIo\RealtimeRegister\Domain\TLDInfo;

class MetadataService
{
    public const DAY_MINUTES = 14400;
    private string $tld;
    private string $provider;
    /** @var TLDInfo */
    private $info;

    /**
     * @throws \Exception
     */
    public function __construct(string $tld)
    {
        $tld = MetadataService::getTld($tld);
        if (strlen($tld) < 2) {
            throw new \Exception('Invalid TLD \'' . $tld . '\'');
        }

        $this->tld = $tld;
        $this->info = Cache::remember('tld.' . $tld, MetadataService::DAY_MINUTES, function () use ($tld) {
            $metadata = App::client()->tlds->info($tld);
            $this->provider = Cache::remember('provider.' . $tld, MetadataService::DAY_MINUTES, function () use ($tld) {
                return $this->getProvider($tld);
            });
            foreach ($metadata->applicableFor as $app_tld) {
                Cache::put('tld.' . $app_tld, $metadata, MetadataService::DAY_MINUTES);
                Cache::put('provider.' . $app_tld, $this->provider, MetadataService::DAY_MINUTES);
            }
            return $metadata;
        });
    }

    public function getAll()
    {
        return $this->info['metadata'];
    }

    /**
     * @param string $param
     * @return string|int|array|bool
     */
    public function get($param)
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
     * @param string $domain the domain name
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

    public function getTldAdditionalFields(): DataObject
    {
        // Only allow registrar to be realtimeregistef
        global $_LANG;

        $languageCodes = $this->get('domainSyntax')['languageCodes'];
        $tldAdditionalFields = [];

        if (!empty($languageCodes)) {
            $tldAdditionalFields[] = [
                'Name' => 'languageCode',
                'LangVar' => 'rtr_languagecode_label',
                'Description' => $_LANG['rtr_languagecode_description'],
                'Type' => 'dropdown',
                'Options' => ',' . implode(',', array_keys($languageCodes)),
            ];
        }

        $properties = $this->get('contactProperties');

        $current = $this->parseCurrentProperties();

        if ($properties) {
            foreach ($properties as $property) {
                $default = array_key_exists($property['name'], $current) ? $current[$property['name']] : null;
                $tldAdditionalFields[] = self::propertyToAdditionalField($this->tld, $property, $default);
            }
        }
        return new DataObject(['fields' => $tldAdditionalFields, 'applicableFor' => $this->info->getApplicableFor()]);
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

        $rtr_contact = App::client()->contacts->get(App::registrarConfig()->customerHandle(), $handle);
        return $rtr_contact->properties[$this->provider] ?? [];
    }

    private function getProvider(string $tld) : string
    {
        $providers = Cache::remember('rtrProviders', self::DAY_MINUTES, function() {
            return App::client()->providers->list(parameters: ["fields" => "tlds,name", "export" => true]);
        });
        foreach ($providers->entities as $provider) {
            $tlds = array_map(fn($tld) => strtolower($tld['name']), $provider['tlds']);
            if (in_array(strtolower($tld), $tlds)) {
                return $provider->name;
            }
        }
        throw new NotFoundException("No provider found for tld");
    }

    public static function toLangVar(string $tld, string $property_name): string {
        return sprintf('tld_%s_%s', strtolower($tld), preg_replace('/[^a-z0-9]/', '', strtolower($property_name)));
    }

    public static function propertyToAdditionalField($tld, $property, $default=null) {
        global $_LANG;
        $langvar = self::toLangVar($tld, $property['name']);
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
                $field['Options'] = self::arrayToOptions($property['values'], $property['name']);
            }
        } else {
            $field['Type'] = 'text';
        }

        if ($default) {
            $field['Default'] = $default;
        }

        return $field;
    }

    public static function isBool($propertyValues): bool {
        if (count($propertyValues) == 1 && array_keys($propertyValues)[0] == 'true') {
            return true;
        }
        if (count($propertyValues['values']) == 2) {
            return count(array_intersect(['true', 'false', 'n', 'y'], $propertyValues)) == 2;
        }
        return false;
    }

    public static function arrayToOptions($array, $propertyName) {
        global $_LANG;

        $options = [];
        foreach ($array as $key => &$value) {
            $translation = sprintf('tld_properties_%s', preg_replace('/[^a-z0-9_]/', '', strtolower($key)));

            $translationKey = sprintf(
                'tld_properties_%s_%s',
                preg_replace('/[^a-z0-9_]/', '', strtolower($propertyName)),
                preg_replace('/[^a-z0-9_]/', '', strtolower($key))
            );

            if (!empty($_LANG[$translationKey])) {
                $value = $_LANG[$translationKey];
            } elseif (!empty($_LANG[$translation])) { // Fallback for when people used old translation keys
                $value = $_LANG[$translation];
            }
            $options[] = self::strip($key) . '|' . self::strip($value);
        }
        return ',' . implode(',', $options);
    }

    public static function strip($str) {
        return str_replace([',', '|'], ' ', $str);
    }
}

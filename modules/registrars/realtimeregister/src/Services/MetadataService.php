<?php

namespace RealtimeRegister\Services;

use RealtimeRegister\Models\Cache;
use RealtimeRegister\Services\Config\Config;
use SandwaveIo\RealtimeRegister\Domain\TLDInfo;
use SandwaveIo\RealtimeRegister\RealtimeRegister;

class MetadataService
{
    public const DAY_MINUTES = 14400;

    private RealtimeRegister $api;
    private string $tld;
    /** @var TLDInfo */
    private $info;

    /**
     * @throws \Exception
     */
    public function __construct(string $tld, $api)
    {
        $this->api = $api;
        $tld = MetadataService::getTld($tld);
        if (strlen($tld) < 2) {
            throw new \Exception('Invalid TLD \'' . $tld . '\'');
        }

        $this->tld = $tld;
        $this->info = Cache::remember('tld.' . $tld, MetadataService::DAY_MINUTES, function () use ($tld) {
            $metadata = $this->api->tlds->info($tld);
            foreach ($metadata->applicableFor as $app_tld) {
                Cache::put('tld.' . $app_tld, $metadata, MetadataService::DAY_MINUTES);
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
}

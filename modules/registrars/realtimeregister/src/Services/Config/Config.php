<?php

namespace RealtimeRegisterDomains\Services\Config;

class Config
{

    public const TLD_INFO_MAPPING = 'tldinfomapping';

    /**
     * Return all or specific property from a config file.
     *
     * @param string $name The config file name or its property full name.
     *
     * @return mixed
     */
    public static function get(string $name)
    {
        $configSettings = explode('.', $name);
        $configFileName = current($configSettings);
        unset($configSettings[0]);

        $configPath = dirname(__DIR__) . '/../../config/';
        $configFile = $configPath . $configFileName . '.php';
        if (!file_exists($configFile)) {
            return false;
        }

        $config = new Repository(include $configFile);

        if (!empty($configSettings)) {
            return $config->get(implode('.', $configSettings));
        }

        return $config->all();
    }

    public static function getPseudoTld(string $tld) {
        $pseudoTld = Config::get(Config::TLD_INFO_MAPPING . '.' . $tld);
        return $pseudoTld ? '.' . $pseudoTld : '';
    }
}

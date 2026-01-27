<?php

namespace RealtimeRegisterDomains\Services;

use RealtimeRegisterDomains\Models\RealtimeRegister\Cache;

class Language
{
    // Cache duration in minutes
    private const CACHE_DURATION = 10080; // 7 days

    public const CACHE_KEY = 'language_translations_';

    public function __construct()
    {
        $_CUSTOMLANG = $this->getTranslationFile();

        global $_LANG;

        if (is_array($_LANG)) {
            if (isset($_LANG['rtr'])) {
                $_LANG['rtr'] = array_merge($_CUSTOMLANG['rtr'], $_LANG['rtr']);
            } else {
                $_LANG = array_merge($_CUSTOMLANG, $_LANG);
            }
        } else {
            $GLOBALS['_LANG'] = $_CUSTOMLANG;
        }
    }

    private function getTranslationFile(): array
    {
        $currentLang = 'english'; // we need a default language

        if (isset($_SESSION)) {
            if (is_null($_SESSION['Language'])) {
                global $CONFIG;
                $currentLang = $CONFIG['Language'];
            } else {
                $currentLang = $_SESSION['Language'];
            }
        }

        // Create a cache key based on the current language
        $cacheKey = self::CACHE_KEY . $currentLang;

        $result = Cache::get($cacheKey);

        if (!$result) {
            $_LANG = [];
            if (file_exists(__DIR__ . '/../../lang/' . $currentLang . ".php")) {
                include(__DIR__ . '/../../lang/' . $currentLang . '.php');
            } else {
                include(__DIR__ . '/../../lang/english.php');
            }
            Cache::put($cacheKey, $_LANG, self::CACHE_DURATION);
        } else {
            $_LANG = $result;
        }

        return $_LANG;
    }
}

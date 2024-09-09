<?php

namespace RealtimeRegister\Services;

class Language
{
    public function __construct()
    {
        $_CUSTOMLANG = $this->getTranslationFile();

        global $_LANG;
        if (isset($_LANG['rtr'])) {
            $_LANG['rtr'] = array_merge($_CUSTOMLANG['rtr'], $_LANG['rtr']);
        } else {
            $_LANG = array_merge($_CUSTOMLANG, $_LANG);
        }
    }

    private function getTranslationFile(): array
    {
        $_LANG = [];

        if (is_null($_SESSION['Language'])) {
            global $CONFIG;
            $currentLang = $CONFIG['Language'];
        } else {
            $currentLang = $_SESSION['Language'];
        }

        if (file_exists(__DIR__ . '/../../lang/' . $currentLang . ".php")) {
            include(__DIR__ . '/../../lang/' . $currentLang . '.php');
        } else {
            include(__DIR__ . '/../../lang/english.php');
        }

        return $_LANG;
    }
}

<?php

namespace RealtimeRegisterDomains\Hooks\Update;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;
use Illuminate\Database\Capsule\Manager as Capsule;

class Banner extends Hook
{
    public function __invoke(DataObject $vars): void
    {
        $json = $this->get();

        if (is_object($json) && $this->isOutdated($json->version)) {
            $GLOBALS['updates_available']['realtimeregister'][] = $json;
        }
    }

    private function get()
    {
        // This data is set in \RealtimeRegisterDomains\Services\UpdateService::check
        $version = Capsule::table('tblregistrars')
            ->where('registrar', 'realtimeregister')
            ->where('setting', 'version_information')->first();

        if ($version == null) {
            return null;
        }

        return json_decode($version->value);
    }

    private function isOutdated($latestVersion): bool
    {
        // little cleanup of versionnumbers
        preg_match('/[0-9][0-9a-z-.]+/', $latestVersion, $latestVersion);

        if (array_key_exists(0, $latestVersion)) {
            return version_compare(App::VERSION, $latestVersion[0], '<');
        }
        return false;
    }
}

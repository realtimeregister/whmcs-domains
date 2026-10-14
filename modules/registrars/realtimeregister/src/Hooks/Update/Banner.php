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
        if (is_object($json)) {
            $GLOBALS['updates_available'][App::NAME][] = $json;
        }
    }

    private function get()
    {
        // This data is set in \RealtimeRegisterDomains\Services\UpdateService::check
        $version = Capsule::table('tblregistrars')
            ->where('registrar', App::NAME)
            ->where('setting', 'version_information')->first();

        if ($version == null) {
            return null;
        }

        return json_decode($version->value);
    }
}

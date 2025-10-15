<?php

namespace RealtimeRegisterDomains\Hooks\Update;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;
use Illuminate\Database\Capsule\Manager as Capsule;

class Banner extends Hook
{
    public function __invoke(DataObject $vars): void
    {
        $json = $this->get();
        if (is_object($json)) {
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
}

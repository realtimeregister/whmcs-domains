<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Models\Whmcs\Domain;
use RealtimeRegister\Services\MetadataService;
use RealtimeRegister\Services\TemplateService;

class SyncExpiry extends Hook
{
    private string $ACTION = 'syncExpiry';

    public function __invoke(DataObject $vars): void
    {
        if ($_POST['action'] === $this->ACTION && $_POST['module'] == 'realtimeregister') {
            if ($_POST['domains']) {
                self::syncDomains($_POST['domains']);
                exit();
            }
            $rtrDomains = App::client()->domains->export(['fields' => 'domainName,expiryDate']);
            $whmcsDomains = array_map(
                fn($domain) => $domain['domain'],
                Domain::query()
                    ->select(['domain'])
                    ->where('registrar', '=', 'realtimeregister')
                    ->get()
                    ->toArray()
            );

            echo TemplateService::renderTemplate(
                "syncDomains.tpl",
                ["fields" => [
                "rtrDomains" => $rtrDomains,
                "whmcsDomains" => $whmcsDomains
                ]]
            );
            exit();
        }
    }

    private static function syncDomains(array $domains): void
    {
        $updated = 0;
        foreach ($domains as $domain) {
            $newExpiryDate = (new MetadataService($domain['domainName']))->getOffsetExpiryDate($domain['expiryDate']);
            $updated += Domain::query()
                ->where('domain', '=', $domain['domainName'])
                ->update(["expiryDate" => $newExpiryDate]);
        }
        echo json_encode(["updated" => $updated]);
    }
}

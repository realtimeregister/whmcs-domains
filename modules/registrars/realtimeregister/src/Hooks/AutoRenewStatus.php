<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Models\Whmcs\Domain;
use RealtimeRegister\Services\TemplateService;

class AutoRenewStatus extends Hook
{
    private string $ACTION = "autoRenew";

    public function __invoke(DataObject $vars): void
    {
        App::assets()->addScript("autoRenew.js");
        App::assets()->addStyle("autorenew.css");

        if ($_POST['action'] === $this->ACTION && $_POST['module'] == 'realtimeregister') {
            if ($_POST['domain']) {
                self::updateDomain($_POST['domain']);
            } else {
                $domains = self::getDomains();
                echo TemplateService::renderTemplate("autoRenew.tpl", ["domains" => $domains]);
            }
            exit();
        }
    }

    private static function getDomains(): array
    {
        $whmcsDomains = array_map(
            fn($domain) => $domain['domain'],
            Domain::query()
                ->select(['domain'])
                ->where('registrar', '=', 'realtimeregister')
                ->whereIn('status', ['active', 'pending'])
                ->get()
                ->toArray()
        );


        $rtrDomains = array_map(
            fn($domain) => $domain['domainName'],
            App::client()->domains->export(
                [
                'fields' => 'domainName',
                'autoRenew' => 'true',
                'autoRenewPeriod:gte' => '12'
                ]
            )
        );

        return array_values(array_intersect($rtrDomains, $whmcsDomains));
    }

    private static function updateDomain(string $domain): void
    {
        try {
            App::client()->domains->update(domainName: $domain, autoRenew: false);
            logActivity(sprintf('Autorenew is set to false for domain: %s', $domain));
            echo json_encode(["updated" => true]);
        } catch (\Exception $e) {
            logActivity('ERROR (Realtime Register): ' . $domain . ': ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(["message" => $domain . ': ' . $e->getMessage()]);
        }
    }
}

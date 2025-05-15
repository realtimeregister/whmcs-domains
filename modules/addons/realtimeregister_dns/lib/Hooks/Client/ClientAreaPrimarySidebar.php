<?php

namespace WHMCS\Module\Addon\RealtimeregisterDns\Hooks\Client;

use RealtimeRegisterDomains\Services\LogService;
use Whmcs\Domain\Domain;
use Whmcs\View\Menu\Item as MenuItem;
use WHMCS\Database\Capsule;

class ClientAreaPrimarySidebar
{
    public function __invoke(MenuItem $primarySidebar, mixed $domain): void
    {
        $isActive = Capsule::table('tbladdonmodules')->select('value')->where('module', 'realtimeregister_dns')->where(
            'setting',
            'active'
        )->first();

        if ($isActive->value === 'on') {
            if (!$domain instanceof Domain) {
                return;
            }
            try {
                $primarySidebar->getChild('Domain Details Management')
                    ->addChild('DNS entries')
                    ->setUri('index.php?m=realtimeregister_dns&id=' . $domain->id)
                    ->setOrder(25);
            } catch (\Exception $e) {
                LogService::logError($e);
            }
        }
    }
}

<?php

namespace RealtimeRegisterDomains\Hooks\Client;

use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;
use Whmcs\Domain\Domain;
use Whmcs\View\Menu\Item as MenuItem;

class ClientAreaPrimarySidebar
{
    public function __invoke(MenuItem $primarySidebar, mixed $domain): void
    {
        if (!$domain instanceof Domain) {
            return;
        }
        try {
            $possibleStatuses = (new MetadataService($domain->domainPunycode))
                ->getMetadata()
                ->possibleClientDomainStatuses ?? [];
            if (!in_array(DomainStatusEnum::STATUS_CLIENT_TRANSFER_PROHIBITED, $possibleStatuses)) {
                $primarySidebar->getChild('Domain Details Management')->removeChild('Registrar Lock Status');
            }
        } catch (\Exception $e) {
            LogService::logError($e);
        }
    }
}

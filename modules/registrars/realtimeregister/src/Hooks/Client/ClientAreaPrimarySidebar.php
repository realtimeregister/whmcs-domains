<?php

namespace RealtimeRegisterDomains\Hooks\Client;

use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegisterDomains\Actions\Domains\DomainTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;
use Whmcs\Domain\Domain;
use Whmcs\View\Menu\Item as MenuItem;

class ClientAreaPrimarySidebar
{
    use DomainTrait;

    public function __invoke(MenuItem $primarySidebar, mixed $domain): void
    {
        if (!$domain instanceof Domain || $domain->registrar != 'realtimeregister') {
            return;
        }
        try {
            $metadata = (new MetadataService($domain->domainPunycode))
                ->getMetadata();
            $possibleStatuses = $metadata->possibleClientDomainStatuses ?? [];
            if (!in_array(DomainStatusEnum::STATUS_CLIENT_TRANSFER_PROHIBITED, $possibleStatuses)) {
                $primarySidebar->getChild('Domain Details Management')->removeChild('Registrar Lock Status');
            }
            if (!$metadata->adjustableAuthCode) {
                $domainInfo = $this->domainInfo($domain->domainPunycode);
                if (!$domainInfo->authcode) {
                    $primarySidebar->getChild('Domain Details Management')->removeChild('Get EPP Code');
                }
            }
        } catch (\Exception $e) {
            LogService::logError($e);
        }
    }
}

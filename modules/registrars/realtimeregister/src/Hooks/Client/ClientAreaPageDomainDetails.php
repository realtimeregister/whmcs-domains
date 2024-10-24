<?php

namespace RealtimeRegisterDomains\Hooks\Client;

use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;

class ClientAreaPageDomainDetails extends Hook
{
    public function __invoke(DataObject $vars): array
    {
        try {
            $possibleStatuses = (new MetadataService(App::toPunycode($vars['domain'])))
                ->getMetadata()
                ->possibleClientDomainStatuses ?? [];
            if (!in_array(DomainStatusEnum::STATUS_CLIENT_TRANSFER_PROHIBITED, $possibleStatuses)) {
                return ["managementoptions" => [...$vars['managementoptions'], 'locking' => false],
                    "lockstatus" => "locked"];
            }
        } catch (\Exception $e) {
            LogService::logError($e);
        }

        return [];
    }
}

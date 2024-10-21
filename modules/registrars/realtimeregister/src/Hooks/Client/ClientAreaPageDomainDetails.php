<?php

namespace RealtimeRegisterDomains\Hooks\Client;

use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;
use TrueBV\Punycode;

class ClientAreaPageDomainDetails extends Hook
{

    public function __invoke(DataObject $vars): array
    {
        try {
            $possibleStatuses = (new MetadataService((new Punycode())->encode($vars['domain'])))
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
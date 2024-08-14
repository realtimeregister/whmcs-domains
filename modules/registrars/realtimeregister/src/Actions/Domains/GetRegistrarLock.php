<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\Enum\DomainStatusEnum;

class GetRegistrarLock extends Action
{
    public function __invoke(Request $request): string
    {
        $domain = $this->domainInfo($request);

        return in_array(DomainStatusEnum::STATUS_CLIENT_TRANSFER_PROHIBITED, $domain->status) ? 'locked' : 'unlocked';
    }
}

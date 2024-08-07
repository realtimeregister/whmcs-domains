<?php

namespace RealtimeRegister\Actions;

use RealtimeRegister\App;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\Enum\DomainStatusEnum;

class SaveRegistrarLock extends Action
{
    public function __invoke(Request $request)
    {
        $domain = $this->domainInfo($request);

        $statuses = array_unique(array_merge($domain->status, [DomainStatusEnum::STATUS_CLIENT_TRANSFER_PROHIBITED]));

        if ($request->get('lockenabled') !== 'locked') {
            unset($statuses[array_search(DomainStatusEnum::STATUS_CLIENT_TRANSFER_PROHIBITED, $statuses)]);
        }

        App::client()->domains->update(
            $request->domain->domainName(),
            statuses: $statuses
        );

        return ['success' => 'success'];
    }
}

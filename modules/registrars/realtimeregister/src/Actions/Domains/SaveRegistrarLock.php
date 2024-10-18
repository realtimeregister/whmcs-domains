<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Exceptions\DomainNotFoundException;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegister\Exceptions\ForbiddenException;
use RealtimeRegister\Exceptions\UnauthorizedException;

class SaveRegistrarLock extends Action
{
    public function __invoke(Request $request): array
    {
        try {
            $domain = $this->domainInfo($request);

            $statuses = array_unique(
                array_merge($domain->status, [DomainStatusEnum::STATUS_CLIENT_TRANSFER_PROHIBITED])
            );

            if ($request->get('lockenabled') !== 'locked') {
                unset($statuses[array_search(DomainStatusEnum::STATUS_CLIENT_TRANSFER_PROHIBITED, $statuses)]);
            }

            App::client()->domains->update(
                domainName: $request->domain->domainName(),
                statuses: array_values($statuses)
            );

            return ['success' => 'success'];
        } catch (BadRequestException | UnauthorizedException | ForbiddenException $exception) {
            throw new DomainNotFoundException($exception);
        }
    }
}

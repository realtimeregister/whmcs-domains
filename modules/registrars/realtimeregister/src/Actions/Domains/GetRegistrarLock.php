<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\Exceptions\DomainNotFoundException;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegister\Exceptions\ForbiddenException;
use RealtimeRegister\Exceptions\UnauthorizedException;

class GetRegistrarLock extends Action
{
    public function __invoke(Request $request): string
    {
        try {
            $domain = $this->domainInfo($request);
            return in_array(
                DomainStatusEnum::STATUS_CLIENT_TRANSFER_PROHIBITED,
                $domain->status
            ) ? 'locked' : 'unlocked';
        } catch (BadRequestException | UnauthorizedException | ForbiddenException $exception) {
            throw new DomainNotFoundException($exception);
        }
    }
}

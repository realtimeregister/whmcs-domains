<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\Exceptions\DomainNotFoundException;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\Enum\DomainStatusEnum;
use SandwaveIo\RealtimeRegister\Exceptions\BadRequestException;
use SandwaveIo\RealtimeRegister\Exceptions\ForbiddenException;
use SandwaveIo\RealtimeRegister\Exceptions\UnauthorizedException;

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

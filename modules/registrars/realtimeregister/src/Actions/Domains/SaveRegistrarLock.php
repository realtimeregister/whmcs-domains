<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Exceptions\DomainNotFoundException;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\Enum\DomainStatusEnum;
use SandwaveIo\RealtimeRegister\Exceptions\BadRequestException;
use SandwaveIo\RealtimeRegister\Exceptions\ForbiddenException;
use SandwaveIo\RealtimeRegister\Exceptions\UnauthorizedException;

class SaveRegistrarLock extends Action
{
    public function __invoke(Request $request)
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
                $request->domain->domainName(),
                statuses: $statuses
            );

            return ['success' => 'success'];
        } catch (BadRequestException | UnauthorizedException | ForbiddenException $exception) {
            throw new DomainNotFoundException($exception);
        }
    }
}

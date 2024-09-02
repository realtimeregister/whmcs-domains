<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Exceptions\BadRequestException;
use SandwaveIo\RealtimeRegister\Exceptions\ForbiddenException;
use SandwaveIo\RealtimeRegister\Exceptions\UnauthorizedException;

class GetAuthCode extends Action
{
    public function __invoke(Request $request): array|string|null
    {
        try {
            $domain = $this->domainInfo($request);
            return $domain->authcode;
        } catch (BadRequestException | UnauthorizedException | ForbiddenException $exception) {
            return ['error' => 'Authcode not available, are you the owner of this domain?'];
        }
    }
}

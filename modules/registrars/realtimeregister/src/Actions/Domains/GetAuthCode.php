<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegister\Exceptions\ForbiddenException;
use RealtimeRegister\Exceptions\UnauthorizedException;

class GetAuthCode extends Action
{
    public function __invoke(Request $request): array
    {
        try {
            $domain = $this->domainInfo($request);

            // might be a limited time authcode, so we re-request the authcode!
            if ($domain->authcode == null) {
                App::client()->domains->update(
                    domainName: self::getDomainName($request->domain),
                    authcode: ""
                );
                $this->forgetDomainInfo($request);
                $domain = $this->domainInfo($request);
            }
            if (!empty($domain->authcode)) {
                return ['eppcode' => $domain->authcode];
            }
            return ['success' => 'success'];
        } catch (BadRequestException | UnauthorizedException | ForbiddenException $exception) {
            return ['error' => 'Authcode not available, are you the owner of this domain?'];
        }
    }
}

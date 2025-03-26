<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegister\Exceptions\ForbiddenException;
use RealtimeRegister\Exceptions\UnauthorizedException;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Exceptions\DomainNotFoundException;
use RealtimeRegisterDomains\Request;

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
                domainName: self::getDomainName($request->domain),
                statuses: array_values($statuses)
            );

            if ($_REQUEST['action'] == 'domaindetails') {
                $url = 'clientarea.php?action=domaindetails&id=' . $_REQUEST['id'] . '#tabReglock';

                // Refresh WHMCS because else you wont see the new status
                header("refresh: 0; url = " . $url);
            }
            return ['success' => true];
        } catch (BadRequestException | UnauthorizedException | ForbiddenException $exception) {
            throw new DomainNotFoundException($exception);
        }
    }
}

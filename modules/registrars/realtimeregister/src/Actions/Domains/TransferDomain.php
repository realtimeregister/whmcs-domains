<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\DomainContactCollection;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Exceptions\ActionFailedException;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\LogService;

class TransferDomain extends Action
{
    use DomainTrait;
    use DomainContactTrait;

    public function __invoke(Request $request): array
    {
        $metadata = $this->metadata($request);
        $domain = $request->domain;

        list(
            'registrant' => $registrant,
            'contacts' => $contacts
            ) = $this->generateContactsForDomain(request: $request, metadata: $metadata);

        App::client()->domains->transfer(
            domainName: $domain->domainName(),
            customer: App::registrarConfig()->customerHandle(),
            registrant: $registrant,
            authcode: $request->eppCode,
            autoRenew: false,
            ns: App::registrarConfig()->keepNameServers() ? null : $domain->nameservers,
            contacts: DomainContactCollection::fromArray($contacts),
        );

        return ['success' => true];
    }

    public static function handleException(\Throwable $exception, array $params): array
    {
        $message = preg_match(
            "/not possible with statuses "
            . "'\[(?:CLIENT|SERVER)_TRANSFER_PROHIBITED(?:, (?:CLIENT|SERVER)_TRANSFER_PROHIBITED)?]'/",
            $exception->getMessage()
        )
            ? $exception->getMessage() . ". Remove the transferlock with the current registrar and retry the transfer."
            : $exception->getMessage();

        LogService::logError($exception, $message);

        return ActionFailedException::forException($exception, $message)->response("RegisterDomain");
    }
}

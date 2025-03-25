<?php

namespace RealtimeRegisterDomains\Actions\Contacts;

use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;

class ResendValidation extends Action
{
    public function __invoke(Request $request): array
    {
        $domain = App::client()->domains->get(self::getDomainName($request->domain));
        if (!in_array(DomainStatusEnum::STATUS_PENDING_VALIDATION, $domain->status)) {
            return ['success' => true, "message" => "Registrant is already validated"];
        }

        $metadata = $this->metadata($request);
        App::client()->contacts->validate(
            App::registrarConfig()->customerHandle(),
            $domain->registrant,
            [$metadata->validationCategory]
        );

        return ['success' => true, "message" => "Successfully resent validation e-mail"];
    }
}

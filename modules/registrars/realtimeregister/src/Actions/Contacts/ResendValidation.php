<?php

namespace RealtimeRegister\Actions\Contacts;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\Enum\DomainStatusEnum;

class ResendValidation extends Action
{
    public function __invoke(Request $request): array
    {
        $domain = App::client()->domains->get($request->domain->domainName());
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

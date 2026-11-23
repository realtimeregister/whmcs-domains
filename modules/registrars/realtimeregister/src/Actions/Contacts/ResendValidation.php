<?php

namespace RealtimeRegisterDomains\Actions\Contacts;

use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\RealtimeRegister\Cache;
use RealtimeRegisterDomains\Request;

class ResendValidation extends Action
{
    public const COOLDOWN_MINUTES = 15;

    public function __invoke(Request $request): array
    {
        $domainName = self::getDomainName($request->domain);
        $cacheKey = 'resend-validation:' . $domainName;
        // Only the client area passes 'cooldown', admins can always resend
        if ($request->get('cooldown') && Cache::get($cacheKey)) {
            return [
                'error' => sprintf(
                    'A validation e-mail was sent recently, please wait %d minutes',
                    self::COOLDOWN_MINUTES
                )
            ];
        }

        $domain = App::client()->domains->get($domainName);
        if (!in_array(DomainStatusEnum::STATUS_PENDING_VALIDATION, $domain->status)) {
            return ['success' => true, "message" => "Registrant is already validated"];
        }

        $metadata = $this->metadata($request);
        App::client()->contacts->validate(
            App::registrarConfig()->customerHandle(),
            $domain->registrant,
            [$metadata->validationCategory]
        );
        Cache::put($cacheKey, true, self::COOLDOWN_MINUTES);

        return ['success' => true, "message" => "Successfully resent validation e-mail"];
    }
}

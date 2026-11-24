<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\Enum\DomainStatusEnum;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\Whmcs\Registrars;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\Config\Config;

class ClientAreaCustomButtonArray extends Action
{
    public function __invoke(Request $request): array
    {
        global $_LANG;

        $client_buttons = [];

        if (!Config::getPseudoTld($request->domain->tldPunyCode ?? $request->domain->tld)) {
            $client_buttons = [
                $_LANG['rtr']['managechildhosts'] => 'ChildHosts'
            ];
        }

        $registrars_dnssec = Registrars::select('value')
            ->where('registrar', App::NAME)
            ->where('setting', 'dnssec')
            ->first();

        if ($registrars_dnssec && $registrars_dnssec->value) {
            $registrars_dnssec_value = decrypt($registrars_dnssec->value, $GLOBALS['cc_encryption_hash']);

            if ($registrars_dnssec_value === 'on') {
                $client_buttons['DNSSec Management'] = 'DNSSec';
            }
        }

        if (($request->params['regtype'] ?? null) !== 'Transfer') {
            try {
                if (
                    !empty($this->metadata($request)->validationCategory)
                    && in_array(
                        DomainStatusEnum::STATUS_PENDING_VALIDATION,
                        App::client()->domains->get(self::getDomainName($request->domain))->status
                    )
                ) {
                    $client_buttons[$_LANG['rtr']['resendvalidation']] = 'ClientResendValidationMails';
                }
            } catch (\Throwable $ignored) {
                // Never break the client area domain page over an optional button
            }
        }

        return $client_buttons;
    }
}

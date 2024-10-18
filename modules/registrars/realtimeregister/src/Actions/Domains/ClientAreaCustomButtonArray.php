<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\Models\Whmcs\Registrars;
use RealtimeRegisterDomains\Request;

class ClientAreaCustomButtonArray extends Action
{
    public function __invoke(Request $request): array
    {
        global $_LANG;

        $client_buttons = [
            $_LANG['rtr']['managechildhosts'] => 'ChildHosts'
        ];

        $registrars_dnssec = Registrars::select('value')
            ->where('registrar', 'realtimeregister')
            ->where('setting', 'dnssec')
            ->first();

        if ($registrars_dnssec && $registrars_dnssec->value) {
            $registrars_dnssec_value = decrypt($registrars_dnssec->value, $GLOBALS['cc_encryption_hash']);

            if ($registrars_dnssec_value === 'on') {
                $client_buttons['DNSSec Management'] = 'DNSSec';
            }
        }

        return $client_buttons;
    }
}

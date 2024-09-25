<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\Models\Whmcs\Registrars;

trait CustomHandlesTrait
{
    use FixAndDecodeJsonTrait;

    public function getCustomHandles(): array
    {
        $customHandleValue = Registrars::select('value')
            ->where('setting', 'customHandles')
            ->registrar()
            ->first();

        if (!empty($customHandleValue->value)) {
            $customHandles = decrypt($customHandleValue->value, $GLOBALS['cc_encryption_hash']);
            return $this->fixAndDecodeJson($customHandles);
        }

        return [];
    }
}

<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\Enum\ResumeTypeEnum;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;

class ResendTransfer extends Action
{
    public function __invoke(Request $request): array
    {
        $processes = App::client()
            ->processes
            ->list(parameters: [
                'status' => 'SUSPENDED',
                "action:in" => "incomingTransfer,incomingInternalTransfer",
                "identifier:eq" => self::getDomainName($request->domain)
            ]);

        if (!$processes->count()) {
            return ['success' => false, "error" => "Transfer does not exist"];
        }

        $process = $processes->entities[0];
        if (in_array(ResumeTypeEnum::TYPE_RESEND, $process->resumeTypes ?? [])) {
            App::client()->processes->resend($process->id);
            return ['success'  => true, "message" => "Transfer successfully resent"];
        }

        return ['success'  => false, "error" => "Transfer can not be resent"];
    }
}

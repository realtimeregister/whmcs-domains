<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Domain\Enum\ResumeTypeEnum;

class ResendTransfer extends Action
{
    public function __invoke(Request $request): array
    {
        $processes = App::client()
            ->processes
            ->list(parameters: [
                'status' => 'SUSPENDED',
                "action:in" => "incomingTransfer,incomingInternalTransfer",
                "identifier:eq" => $request->domain->domainName()
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

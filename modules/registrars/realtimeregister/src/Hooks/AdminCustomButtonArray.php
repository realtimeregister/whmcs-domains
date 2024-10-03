<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Models\Whmcs\Domain;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\Enum\ResumeTypeEnum;

class AdminCustomButtonArray extends Action
{
    public function __invoke(Request $request): array|string
    {
        $metadata = $this->metadata($request);

        $adminButtons = [
            "Sync expiry date" => "SyncExpiryDate",
            "Sync domein" => "Sync"
        ];

        $whmcsDomain = Domain::find($request->params['domainid']);
        if ($whmcsDomain['status'] === 'Pending') {
            if ($whmcsDomain['type'] === 'Register') {
                $adminButtons['Register and accept billables'] = "RegisterWithBillables";
            }
            if ($whmcsDomain['type'] === 'Transfer') {
                $adminButtons['Transfer and accept billables'] = "TransferWithBillables";
            }
        }

        try {
            $domain = $request->domain->domainName();
            if (!empty($metadata->transferFOA)) {
                $processes = App::client()->processes->list(
                    parameters: [
                        'status' => 'SUSPENDED',
                        'action:in' => 'incomingInternalTransfer,incomingTransfer',
                        "identifier:eq" => $request->domain->domainName()
                    ]
                );

                if (
                    $processes->count() > 0
                    && in_array(ResumeTypeEnum::TYPE_RESEND, $processes->entities[0]->resumeTypes ?? [])
                ) {
                    $adminButtons['Resend FOA'] = "ResendTransfer";
                }
            }
            if ($request->params['regtype'] !== 'Transfer' && !empty($metadata->validationCategory)) {
                try {
                    $info = App::client()->domains->get($domain);
                    if (in_array('PENDING_VALIDATION', $info->status)) {
                        $adminButtons['Resend validation mails'] = "ResendValidationMails";
                    }
                } catch (\SandwaveIo\RealtimeRegister\Exceptions\NotFoundException $exception) {
                    // Don't care about this exception at this point in the code
                } catch (\Exception $ex) {
                    if (!str_contains($ex->getMessage(), 'Entity not found')) {
                        throw $ex;
                    }
                }
            }
        } catch (\Exception $ex) {
            return sprintf('Error retrieving information about domain: %s.', $ex->getMessage());
        }

        return $adminButtons;
    }
}

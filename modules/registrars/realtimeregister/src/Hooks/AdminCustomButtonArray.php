<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Models\Domain;
use RealtimeRegister\Request;
use RealtimeRegister\Services\MetadataService;

class AdminCustomButtonArray extends Action
{
    public function __invoke(Request $request): array|string
    {
        $adminButtons = [
            "Sync expiry date" => "SyncExpiryDate"
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
            $metadataService = new MetadataService($request->params['tld']);
            $metadata = $metadataService->getMetadata();
            if (empty($metadata)) {
                return $adminButtons;
            }

            $domain = $request->params['sld'] . '.' . $request->params['tld'];
            if (!empty($metadata->transferFOA)) {
                $processes = App::client()->processes->list(
                    1,
                    0,
                    null,
                    ['action' => 'incomingInternalTransfer']
                );

                if ($processes->count() > 0 && $processes->entities[0]->status === 'SUSPENDED') {
                    $adminButtons['Resend FOA'] = "ResendTransfer";
                }
            }

            if ($request->params['regtype'] !== 'Transfer' && !empty($metadata->validationCategory)) {
                try {
                    $info = App::client()->domains->get($domain);

                    if (in_array('PENDING_VALIDATION', $info->status)) {
                        $adminButtons['Resend validation mails'] = "ResendValidationMails";
                    }
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

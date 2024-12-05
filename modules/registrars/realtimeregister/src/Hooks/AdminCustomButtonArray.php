<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegister\Domain\Enum\ResumeTypeEnum;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Enums\ScriptLocationType;
use RealtimeRegisterDomains\Models\Whmcs\Domain;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\LogService;

class AdminCustomButtonArray extends Action
{
    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): array|string
    {
        $metadata = $this->metadata($request);

        $adminButtons = [
            "Sync domain" => "ManualSync"
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

        $adminButtons['Renew and accept billables'] = "RenewDomainWithBillables";

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
            } catch (\RealtimeRegister\Exceptions\NotFoundException $exception) {
                // Don't care about this exception at this point in the code
            } catch (\Exception $ex) {
                if (!str_contains($ex->getMessage(), 'Entity not found')) {
                    throw $ex;
                }
            }
        }

        App::assets()->addScript('renew.js', ScriptLocationType::Footer);

        return $adminButtons;
    }

    public static function handleException(\Throwable $exception, array $params): array
    {
        LogService::logError($exception);
        return [
            "success" => false,
            "message" => sprintf('Error retrieving information about domain: %s.', $exception->getMessage())
        ];
    }
}

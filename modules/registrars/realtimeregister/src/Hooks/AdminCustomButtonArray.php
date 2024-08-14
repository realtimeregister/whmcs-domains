<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Models\Domain;
use RealtimeRegister\Services\MetadataService;

class AdminCustomButtonArray extends Hook
{
    public function __invoke(DataObject $vars)
    {
        $adminButtons = [
            "Sync expiry date" => "sync_expiry_date"
        ];

        $whmcsDomain = Domain::find($vars->get('domainid'));
        if ($whmcsDomain['status'] === 'Pending') {
            if ($whmcsDomain['type'] === 'Register') {
                $adminButtons['Register and accept billables'] = "register_with_billables";
            }
            if ($whmcsDomain['type'] === 'Transfer') {
                $adminButtons['Transfer and accept billables'] = "transfer_with_billables";
            }
        }

        try {
            $metadataService = new MetadataService($vars->get('tld'), App::client());
//            $metadata = RtrApiService::getMetaData($params['tld']);
            $metadata = $metadataService->get($vars->get('tld'));
            if (empty($metadata)) {
                return $adminButtons;
            }

            $domain = $vars->get('sld') . '.' . $vars->get('tld');

            if (!empty($metadata['transferFOA'])) {
                $processes = RtrApiService::getProcesses($domain, 1, ['action' => 'incomingInternalTransfer']);

                if (!empty($processes['entities'][0]) && $processes['entities'][0]['status'] === 'SUSPENDED') {
                    $adminButtons['Resend FOA'] = "resend_transfer";
                }
            }

            if ($vars->get('regtype') !== 'Transfer' && !empty($metadata['metadata']['validationCategory'])) {
                try {
                    $info = RtrApiService::getDomain($domain);

                    if (in_array('PENDING_VALIDATION', $info['status'])) {
                        $adminButtons['Resend validation mails'] = "resend_validation_mails";
                    }
                } catch (\Exception $ex) {
                    if (!str_contains($ex->getMessage(), 'Entity not found')) {
                        throw $ex;
                    }
                }
            }
        } catch (\Exception $ex) {
            return rtrApiClient::instance()->error('Error retrieving information about domain: %s.', $ex->getMessage());
        }

        return $adminButtons;
    }
}
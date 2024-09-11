<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\Actions\Domains\SmartyTrait;
use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Models\Whmcs\Domain;

class AdminClientDomainsTabFields extends Hook
{
    use SmartyTrait;

    public function __invoke(DataObject $vars): array
    {
        $domainInfo = Domain::find($vars->get('id'));
        $domainName = $domainInfo->domain;

        $fields = [];
        try {
            $result = App::client()->domains->transferInfo($domainName);
            if (!empty($result->log)) {
                $fields['Transfer Logs'] = $this->render(__DIR__ . '/../Assets/Tpl/admin/transfer_log.tpl', [
                    'type' => $result->type,
                    'logs' => array_reverse($result->log->entities)
                ]);
            }
        } catch (\Exception) {
            # ignore
        }

        try {
            $processes = App::client()->processes->export([
                'fields' => 'createdDate,action,status',
                'order' => '-createdDate',
                'identifier:eq' => $domainName
            ]);
            if (!empty($processes)) {
                $fields['Processes'] = $this->render(__DIR__ . '/../Assets/Tpl/admin/processes_log.tpl', [
                    'processes' => $processes,
                ]);
            }
        } catch (\Exception) {
            # ignore
        }

        try {
            $rtrDomain = App::client()->domains->get($domainName);

            if (!empty($rtrDomain->keyData)) {
                $fields['DNSSec'] = $this->render(
                    __DIR__ . '/../Assets/Tpl/admin/keydata.tpl',
                    ['keyData' => $rtrDomain->keyData->entities]
                );
            }
        } catch (\Exception) {
            # ignore
        }
        if (!empty($fields)) {
            $fields = array_merge(['' => '<h1>Information from Realtime Register:</h1>'], $fields);
        }
        return $fields;
    }
}

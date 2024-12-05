<?php

namespace RealtimeRegisterDomains\Hooks\Admin;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Hooks\Hook;
use RealtimeRegisterDomains\Services\LogService;

class ErrorLogQuery extends Hook
{
    public function __invoke(DataObject $vars): void
    {
        if (in_array('Configure Custom Client Fields', $vars->get('admin_perms'))) {
            if ($_POST['action'] === 'fetchErrorLogEntries') {
                $pageId = array_key_exists('pageId', $_REQUEST) ? (int)$_REQUEST['pageId'] : 1;
                $logData = LogService::getErrors($pageId);

                echo json_encode(
                    [
                        'result' => 'success',
                        'logEntries' => $logData->items(),
                        'pageId' => $logData->currentPage(),
                        'hasMorePages' => $logData->hasMorePages()
                    ]
                );
                die();
            }
        }
    }
}

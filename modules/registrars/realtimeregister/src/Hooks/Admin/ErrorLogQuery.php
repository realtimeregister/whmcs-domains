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
                $searchTerm = array_key_exists('searchTerm', $_REQUEST) ? (string)$_REQUEST['searchTerm'] : '';
                $logData = LogService::getErrors($pageId, $searchTerm);

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

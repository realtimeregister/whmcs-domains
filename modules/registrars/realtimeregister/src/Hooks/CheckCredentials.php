<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;

class CheckCredentials extends Hook
{
    private string $ACTION = 'checkConnection';

    public function __invoke(DataObject $vars): void
    {
        if ($_POST['action'] === $this->ACTION && $_POST['module'] == 'realtimeregister') {
            App::assets()->addScript("checkCredentials.js");
            self::checkConnection();
        }
    }

    private static function checkConnection(): void
    {
        $apiKey = preg_match('/\*+/', $_POST['apiKey'])
            ? App::registrarConfig()->apiKey()
            : $_POST['apiKey'];

        try {
            $brands = App::standalone($apiKey, $_POST['ote'] === 'true')->brands->list($_POST['handle']);
            if ($brands->count() == 0) {
                $response = [
                    'status' => 'error',
                    'msg' => 'Customer not found',
                    'code' => 404
                ];
            } else {
                $response = [
                    'status' => 'success',
                    'connection' => 'true',
                    'msg' => 'Connection Successful'
                ];
            }
        } catch (\Exception $e) {
            logActivity("Error while checking connection for Realtime Register: " . $e->getMessage());
            $response = [
                'status' => 'error',
                'msg' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }

        echo json_encode($response);
        exit;
    }
}

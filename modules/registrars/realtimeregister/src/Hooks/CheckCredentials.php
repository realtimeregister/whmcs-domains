<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;

class CheckCredentials extends Hook
{

    private string $ACTION = 'checkConnection';

    public function __invoke(DataObject $vars): void
    {
        App::assets()->addScript("checkCredentials.js");

        if ($_POST['action'] === $this->ACTION && $_POST['module'] == 'realtimeregister') {
            self::checkConnection();
        }

    }

    private static function checkConnection(): void
    {
        $apiKey = preg_match('/\*+/', $_POST['apiKey'])
            ? App::registrarConfig()->apiKey()
            : $_POST['apiKey'];

        try {
            App::standalone($apiKey, $_POST['ote'] === 'true')->brands->list($_POST['handle']);
            $response = [
                'status' => 'success',
                'connection' => 'true',
                'msg' => 'Connection Successful'
            ];
        } catch (\Exception $e) {
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

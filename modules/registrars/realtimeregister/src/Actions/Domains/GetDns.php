<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;

class GetDns extends Action
{
    public function __invoke(Request $request): array
    {
        if (
            $request->params['dnsmanagement'] === true
            && App::registrarConfig()->hasDnsSupport()
        ) {
            $domain = $this->domainInfo($request);

            /** @var  $zone */
            $zone = App::client()->domains->get($domain->domainName)->zone;

            if ($zone && $zone->id !== null && $zone->master === null && $zone->template === null) {
                $dataFromServer = App::client()->dnszones->get($zone->id);

                // Saveguard for zones which are saved, but don't have any records
                if ($dataFromServer->records !== null) {
                    $records = $dataFromServer->records->toArray();

                    // If there are any quotes in the record, we have problems in the html, so we html encode those
                    foreach ($records as $k => $record) {
                        $records[$k]['content'] = htmlentities($record['content']);
                    }
                    $vars['zones'] = $records;
                }

                $vars['soa'] = [
                    'hostmaster' => $dataFromServer->hostMaster,
                    'refresh' => $dataFromServer->refresh,
                    'retry' => $dataFromServer->retry,
                    'expire' => $dataFromServer->expire,
                    'ttl' => $dataFromServer->ttl,
                ];

                if (
                    isset(
                        $_SESSION['rtr']['dns'],
                        $_SESSION['rtr']['dns']['error']
                    )
                ) {
                    $vars['formerrors'] = $_SESSION['rtr']['dns']['error'];
                    /**
                     * we probably had a problem while saving, so we overwrite the data we got from Realtime Register,
                     * and reinsert our previous data
                     */
                    $vars['zones'] = $_SESSION['rtr']['dns']['dns-items'];
                    unset($_SESSION['rtr']['dns']['error']);
                    unset($_SESSION['rtr']['dns']['dns-items']);
                }
                if (isset($_SESSION['rtr']['dns'], $_SESSION['rtr']['dns']['success'])) {
                    $vars['status']['success'] = $_SESSION['rtr']['dns']['success'];
                    unset($_SESSION['rtr']['dns']['success']);
                }
                return $vars;
            }
        } else {
            throw new \Exception('DNS management not enabled on this domain');
        }
    }
}

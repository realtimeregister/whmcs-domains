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

                $vars['zones'] = $dataFromServer->records->toArray();
                $vars['soa'] = [
                    'hostmaster' => $dataFromServer->hostMaster,
                    'refresh' => $dataFromServer->refresh,
                    'retry' => $dataFromServer->retry,
                    'expire' => $dataFromServer->expire,
                    'ttl' => $dataFromServer->ttl,
                ];
                return $vars;
            }
        } else {
            throw new \Exception('DNS management not enabled on this domain');
        }
    }
}

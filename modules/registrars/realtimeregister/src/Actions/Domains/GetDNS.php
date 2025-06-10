<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegister\Api\DnsZonesApi;
use RealtimeRegister\RealtimeRegister as RTRClient;

class GetDNS extends Action
{
    public function __invoke(\RealtimeRegisterDomains\Request $request)
    {
        try {
            $domainInfo = $this->domainInfo($request);
            $domain = $domainInfo->domainName ?? null;
            $zoneId = $domainInfo->zone->id ?? null;
            $nameservers = $domainInfo->ns ?? [];

            if (!$domain) {
                return ['error' => 'Domainname not found.'];
            }


                if (!$zoneId) {
                    return ['error' => 'Zone ID not available.'];
                }

                $apiKey = $request->params['rtr_api_key'] ?? null;
                if (empty($apiKey)) {
                    return ['error' => 'RTR API-Key not configured.'];
                }

                $client = new RTRClient($apiKey);
                $dnsApi = $client->dnszones;
                $zone = $dnsApi->get($zoneId);
                $records = $zone->records->entities;

                return array_map(function ($record) {
                    return [
                        'hostname' => $record->name ?? '',
                        'type'     => strtoupper($record->type ?? ''),
                        'address'  => $record->content ?? '',
                        'priority' => $record->prio ?? '',
                    ];
                }, $records);

        } catch (\Throwable $e) {
            LogService::logError($e);
            return ['error' => 'Error while getting DNS: ' . $e->getMessage()];
        }
    }
}

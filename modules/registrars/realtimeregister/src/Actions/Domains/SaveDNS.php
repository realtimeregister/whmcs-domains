<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegister\Api\DnsZonesApi;
use RealtimeRegister\RealtimeRegister as RTRClient;

class SaveDNS extends Action
{
    public function __invoke(\RealtimeRegisterDomains\Request $request)
    {
        try {
            $domainInfo = $this->domainInfo($request);
            $domain = $domainInfo->domainName ?? null;
            $zoneId = $domainInfo->zone->id ?? null;
            $nameservers = $domainInfo->ns ?? [];

            if (!$domain) {
                return ['error' => 'Domainname nnot found.'];
            }

            $dnsrecords = $request->params['dnsrecords'] ?? [];

			if (!$zoneId) return ['error' => 'Zone ID not available.'];
			$apiKey = $request->params['rtr_api_key'] ?? null;
			if (empty($apiKey)) return ['error' => 'RTR API-Key not configured.'];

			$client = new RTRClient($apiKey);

			$records = array_map(function ($r) use ($domain) {
				$name = trim($r['hostname'] ?? '');
				if ($name === '@') $name = $domain;
				elseif (strpos($name, '.') === false) $name .= '.' . $domain;

				return [
					'name'    => $name,
					'type'    => strtoupper($r['type']),
					'content' => $r['address'],
					'ttl'     => 3600,
					'prio'    => $r['priority'] ?? null,
				];
			}, array_filter($dnsrecords, fn($r) => !empty($r['address'])));

			$client->dnszones->update(
				$zoneId,
				null, null, null, null, null, null, null, null, null, null, null,
				\RealtimeRegister\Domain\DomainZoneRecordCollection::fromArray($records)
			);

			return ['success' => true];

        } catch (\Throwable $e) {
            LogService::logError($e);
            return ['error' => 'Error while saving DNS values: ' . $e->getMessage()];
        }
    }
}

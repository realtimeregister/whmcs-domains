<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Exceptions\RealtimeRegisterClientException;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\Whmcs\Orders;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\LogService;

class SaveNameservers extends Action
{

    use DomainTrait;

    public function __invoke(Request $request): array
    {
        try {
            App::client()->domains->update(
                domainName: $request->domain->domainName(),
                ns: $request->domain->nameservers
            );

            $this->forgetDomainInfo($request);

            return ['success' => true];
        } catch (RealtimeRegisterClientException $exception) {
            $order = App::localApi()->getDomainOrder($request->params['domainid'], $request->params['userid']);
            if (($order['status'] == 'Pending')) {
                Orders::query()
                    ->where('id', '=', $order['id'])
                    ->update(['nameservers' => implode(",", $request->domain->nameservers)]);
                return ['success' => true];
            }
            LogService::logError($exception);
            return ['error' => sprintf('Error fetching domain information: %s', $exception->getMessage())];
        }
    }
}

<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\BillableCollection;
use RealtimeRegister\Domain\DomainContactCollection;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\Whmcs\Domain;
use RealtimeRegisterDomains\Models\Whmcs\Orders;
use RealtimeRegisterDomains\Request;

class TransferWithBillables extends Action
{
    use DomainTrait;
    use DomainContactTrait;

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): array
    {
        $metadata = $this->metadata($request);
        $domain = $request->domain;
        $ns = $this->getDomainNameservers($request);
        list(
            'registrant' => $registrant,
            'contacts' => $contacts
            ) = $this->generateContactsForDomain($request, $metadata);

        $whmcsDomain = Domain::query()->find($request->params['domainid']);
        $order = Orders::query()->find($whmcsDomain->orderid);
        $parameters = [
            'domainName' => $domain->domainName(),
            'customer' => App::registrarConfig()->customerHandle(),
            'registrant' => $registrant,
            'ns' => App::registrarConfig()->keepNameServers() ? null : $ns,
            'authcode' => html_entity_decode(unserialize($order->transfersecret)[$domain->domainName()]),
            'autoRenew' => false,
            'contacts' => DomainContactCollection::fromArray($contacts),
            'isQuote' => true
        ];

        $billables = $this->buildBillables(App::client()->domains->transfer(...$parameters));

        $parameters['isQuote'] = false;
        $parameters['billables'] = BillableCollection::fromArray($billables);

        App::client()->domains->transfer(...$parameters);
        return ['success' => true];
    }
}

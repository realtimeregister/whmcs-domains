<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Models\Whmcs\Domain;
use RealtimeRegister\Models\Whmcs\Orders;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\BillableCollection;
use SandwaveIo\RealtimeRegister\Domain\DomainContactCollection;

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
        list(
            'registrant' => $registrant,
            'contacts' => $contacts
            ) = $this->generateContactsForDomain($request, $metadata);

        $whmcsDomain = Domain::query()->find($request->params['domainid']);
        $order = Orders::query()->find($whmcsDomain->orderid)->first();
        $parameters = [
            'domainName' => $domain->domainName(),
            'customer'=> App::registrarConfig()->customerHandle(),
            'registrant' => $registrant,
            'ns' => App::registrarConfig()->keepNameServers() ? null : $domain->nameservers,
            'authcode' => html_entity_decode(unserialize($order->transfersecret)[$domain->domainName()]),
            'autoRenew'=> false,
            'contacts'=> DomainContactCollection::fromArray($contacts),
            'isQuote' => true
        ];

        $billables = $this->buildBillables(App::client()->domains->transfer(...$parameters));

        $parameters['isQuote'] = false;
        $parameters['billables'] = BillableCollection::fromArray($billables);

        App::client()->domains->transfer(...$parameters);
        return ['success' => true];
    }
}
<?php

namespace RealtimeRegister\Actions\Domains;

use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\Billable;
use SandwaveIo\RealtimeRegister\Domain\BillableCollection;
use SandwaveIo\RealtimeRegister\Domain\DomainContactCollection;
use SandwaveIo\RealtimeRegister\Domain\DomainQuote;
use SandwaveIo\RealtimeRegister\Domain\DomainRegistration;
use Illuminate\Database\Capsule\Manager as Capsule;

class RegisterWithBillables extends Action
{
    use DomainTrait;

    public function __invoke(Request $request): array|string
    {
        $params = $this->getDomainNameservers(params: $request->params);
        $metadata = $this->metadata($request);

        try {
            $orderId = App::localApi()->domain(
                clientId: $request->get('userid'),
                domainId: $request->get('domainid')
            )->get('orderid');

            $contactId = App::localApi()->order(id: $orderId)->get('contactid');

            $registrant = $this->getOrCreateContact(
                clientId: $request->get('userid'),
                contactId: $contactId,
                role: 'ADMIN',
                organizationAllowed: $metadata->registrant->organizationAllowed
            );
            $contacts = [];
            foreach (self::$CONTACT_ROLES as $role => $name) {
                $organizationAllowed = $metadata->{$name}->organizationAllowed;
                $contacts[] = [
                    'role' => $role,
                    'handle' => $this->getOrCreateContact(
                        clientId: $request->get('userid'),
                        contactId: $contactId,
                        role: $role,
                        organizationAllowed: $organizationAllowed
                    )
                ];
            }

            $billables = $this->buildBillables(
                App::client()->domains->register(
                    domainName: $request->domain->domainName(),
                    customer: App::registrarConfig()->customerHandle(),
                    registrant: $registrant,
                    contacts: DomainContactCollection::fromArray($contacts),
                    isQuote: true,
                )
            );
        } catch (\Exception $ex) {
            return ['error' => $ex->getMessage()];
        }

        try {
            if ($params['idprotection']) {
                $billables[] = [
                    'product' => 'domain_' . $params['tld'],
                    'action' => 'PRIVACY_PROTECT',
                    'quantity' => $params['regperiod'],
                ];
            }

            /**
 * @var DomainRegistration $registeredDomain 
*/
            $registeredDomain = App::client()->domains->register(
                domainName: $request->domain->domainName(),
                customer: App::registrarConfig()->customerHandle(),
                registrant: $registrant,
                contacts: DomainContactCollection::fromArray($contacts),
                billables: BillableCollection::fromArray($billables),
            );
        } catch (\Exception $ex) {
            return sprintf(
                'Error creating domain %s. Error details: %s.',
                $registeredDomain->domainName,
                $ex->getMessage()
            );
        }

        $fields = $this->getDueAndExpireDate(expiryDate: $registeredDomain->expiryDate, metadata: $metadata);
        Capsule::table("tbldomains")->where('id', $params['id'] ?: $params['domainid'])->update($fields);

        return ['success' => true];
    }

    private function buildBillables(DomainQuote $quote): array
    {
        $billables = [];
        if (!empty($quote->quote->billables) && $quote->quote->billables->count() > 1) {
            /**
 * @var Billable $billable 
*/
            foreach ($quote->quote->billables as $billable) {
                $billables[] = [
                    'action' => $billable->action,
                    'product' => $billable->product,
                    'quantity' => $billable->quantity
                ];
            }
        }
        return $billables;
    }
}
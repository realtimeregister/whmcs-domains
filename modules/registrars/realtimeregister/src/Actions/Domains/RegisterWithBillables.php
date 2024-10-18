<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use Illuminate\Database\Capsule\Manager as Capsule;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Request;
use RealtimeRegister\Domain\BillableCollection;
use RealtimeRegister\Domain\DomainContactCollection;
use RealtimeRegister\Domain\DomainRegistration;

class RegisterWithBillables extends Action
{
    use DomainTrait;

    public function __invoke(Request $request): array|string
    {
        $params = $this->getDomainNameservers(params: $request->params);
        $metadata = $this->metadata($request);

        try {
            $domainName = $this->checkForPunyCode($request->domain);

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

            $parameters = [
                'domainName' => $domainName,
                'customer' => App::registrarConfig()->customerHandle(),
                'registrant' => $registrant,

                'contacts' => DomainContactCollection::fromArray($contacts),
                'isQuote' => true,
            ];

            if ($request->domain->idnLanguage) {
                $parameters['languageCode'] = $request->domain->idnLanguage;
            }

            $billables = $this->buildBillables(
                App::client()->domains->register(...$parameters)
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

            /** @var DomainRegistration $registeredDomain */
            $parameters = [
                'domainName' => $domainName,
                'customer' => App::registrarConfig()->customerHandle(),
                'registrant' => $registrant,

                'contacts' => DomainContactCollection::fromArray($contacts),
                'billables' => BillableCollection::fromArray($billables),
            ];

            if ($request->domain->idnLanguage) {
                $parameters['languageCode'] = $request->domain->idnLanguage;
            }

            $registeredDomain = App::client()->domains->register(...$parameters);
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
}

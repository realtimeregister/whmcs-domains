<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use RealtimeRegister\Domain\BillableCollection;
use RealtimeRegister\Domain\DomainContactCollection;
use RealtimeRegister\Domain\DomainRegistration;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\Whmcs\Domain;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\LogService;

class RegisterWithBillables extends Action
{
    use DomainTrait;

    public function __invoke(Request $request): array|string
    {
        $params = $request->params;
        $metadata = $this->metadata($request);
        $ns = $this->getDomainNameservers($request);

        try {
            $domainName = $request->domain->domainName();

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
                'ns' => $ns,
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
            LogService::logError($ex);
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
                'ns' => $ns,
                'contacts' => DomainContactCollection::fromArray($contacts),
                'billables' => BillableCollection::fromArray($billables),
            ];

            if ($request->domain->idnLanguage) {
                $parameters['languageCode'] = $request->domain->idnLanguage;
            }

            $registeredDomain = App::client()->domains->register(...$parameters);
        } catch (\Exception $ex) {
            LogService::logError($ex);
            return [
                'error' => sprintf(
                    'Error creating domain %s. Error details: %s.',
                    $registeredDomain->domainName,
                    $ex->getMessage()
                )
            ];
        }

        if ($registeredDomain->expiryDate) {
            $fields = $this->getDueAndExpireDate(expiryDate: $registeredDomain->expiryDate, metadata: $metadata);
            Domain::query()->where('id', $params['domainid'])->update($fields);
        }

        return ['success' => true];
    }
}

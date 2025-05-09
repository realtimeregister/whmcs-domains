<?php

namespace RealtimeRegisterDomains\Actions\Domains;

use Illuminate\Database\Capsule\Manager as Capsule;
use RealtimeRegister\Domain\Billable;
use RealtimeRegister\Domain\DomainQuote;
use RealtimeRegister\Domain\TLDMetaData;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Entities\Domain;
use RealtimeRegisterDomains\Models\RealtimeRegister\ContactMapping;
use RealtimeRegisterDomains\Models\Whmcs\Configuration;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\Config\Config;
use RealtimeRegisterDomains\Services\ContactService;
use RealtimeRegisterDomains\Services\MetadataService;

trait DomainTrait
{
    protected static array $CONTACT_ROLES = [
        "TECH" => "techContacts",
        "ADMIN" => "adminContacts",
        "BILLING" => "billingContacts"
    ];

    // Check if we override the handle in the settings
    protected function handleOverride(string $role): ?string
    {
        return match ($role) {
            'ADMIN' => App::registrarConfig()->get('handle'),
            'BILLING' => App::registrarConfig()->get('handle_billing'),
            'TECH' => App::registrarConfig()->get('handle_tech'),
            default => null
        };
    }

    protected function getOrCreateContact(int $clientId, int $contactId, bool $organizationAllowed, string $role = null)
    {
        if ($role) {
            $handle = $this->handleOverride($role);

            if ($handle) {
                return $handle;
            }
        }

        // Check if we have a contact mapping
        $handle = ContactMapping::query()
            ->where('userid', $clientId)
            ->where('contactid', $contactId)
            ->where('org_allowed', $organizationAllowed)
            ->value('handle');

        if ($handle) {
            return $handle;
        }

        // Fetch the whmcs contact
        $whmcsContact = App::localApi()->getContact($clientId, $contactId);

        // Try and match the whmcs contact to a rtr contact
        $contact = ContactService::findRemote(
            new DataObject(
                [
                    'organization' => $whmcsContact->get('companyname'),
                    'name' => $whmcsContact->get('fullname'),
                    'email' => $whmcsContact->get('email'),
                    'country' => $whmcsContact->get('countrycode')
                ]
            ),
            $organizationAllowed
        );

        if (!$contact) {
            // If we do not find a match we create a new contact
            $rtrContact = ContactService::convertToRtrContact($whmcsContact, $organizationAllowed);
            $handle = uniqid(App::registrarConfig()->contactHandlePrefix() ?: '', true);

            App::client()->contacts->create(
                customer: App::registrarConfig()->customerHandle(),
                handle: $handle,
                name: $rtrContact->get('name'),
                addressLine: $rtrContact->get('addressLine'),
                postalCode: $rtrContact->get('postalCode'),
                city: $rtrContact->get('city'),
                country: $rtrContact->get('country'),
                email: $rtrContact->get('email'),
                voice: $rtrContact->get('voice') ?: '',
                organization: $rtrContact->get('organization'),
                state: $rtrContact->get('state')
            );

            App::contacts()->addContactMapping($clientId, $contactId, $handle, $organizationAllowed);

            return $handle;
        }
        return $contact['handle'];
    }

    protected function getDomainNameservers(Request $request, DataObject $order = null): array
    {
        if (!empty($request->domain->nameservers)) {
            return $request->domain->nameservers;
        }
        $domainOrder = $order ?? App::localApi()
            ->getDomainOrder($request->params['domainid'], $request->params['userid']);
        if (!empty($domainOrder['nameservers'])) {
            return explode(',', $domainOrder['nameservers']);
        }
        return array_values(array_filter([
            Configuration::query()->where('setting', 'DefaultNameserver1')->value('value'),
            Configuration::query()->where('setting', 'DefaultNameserver2')->value('value'),
            Configuration::query()->where('setting', 'DefaultNameserver3')->value('value'),
            Configuration::query()->where('setting', 'DefaultNameserver4')->value('value'),
            Configuration::query()->where('setting', 'DefaultNameserver5')->value('value')
        ]));
    }

    /**
     * Returns an array with the due date / expire date
     * Due date: date format with posible WHMCS offset
     * Expire date with offset expiryDateOffset (metadata) in seconds
     */
    public static function getDueAndExpireDate(\DateTime $expiryDate, TLDMetaData $metadata): array
    {
        $offsetExpiryDate = self::getOffsetExpiryDate($expiryDate, $metadata);
        $duedate = self::getSyncDueDate($offsetExpiryDate);

        return [
            'expirydate' => $offsetExpiryDate,
            'nextduedate' => $duedate,
            'nextinvoicedate' => $duedate,
        ];
    }

    /**
     * Sync domain expire date with realtimeregister this includes the expireDateOffset
     */
    public static function syncExpireDate(array $params, TLDMetaData $metadata): void
    {
        $offset = $metadata->expiryDateOffset;

        $domainInformation = App::client()->domains->get($params['sld'] . '.' . $params['tld']);
        $fields['expirydate'] = date("Y-m-d", $domainInformation->expiryDate->getTimestamp() - ((int)$offset));
        Capsule::table("tbldomains")->where('id', $fields['id'] ?: $params['domainid'])->update($fields);
    }

    public static function getOffsetExpiryDate(\DateTime $expiryDate, TLDMetaData $metadata): \DateTime
    {
        $offset = $metadata->expiryDateOffset;
        return \DateTime::createFromFormat('U', $expiryDate->getTimestamp() - ((int)$offset));
    }

    /**
     * Get domain sync due date
     */
    public static function getSyncDueDate(\DateTime $date): \DateTime
    {
        $domainSyncNextDuaDateDays = Capsule::table(
            "tblconfiguration"
        )->where("setting", "DomainSyncNextDueDate")->value("value");
        if ($domainSyncNextDuaDateDays) {
            $syncOffset = (int)$domainSyncNextDuaDateDays;

            return \DateTime::createFromFormat('U', ($date->getTimestamp() + $syncOffset) - (24 * 3600));
        }

        return $date;
    }

    private function buildBillables(DomainQuote $quote): array
    {
        $billables = [];
        if (!empty($quote->quote->billables) && $quote->quote->billables->count() >= 1) {
            /**
             * @var Billable $billable
             */
            foreach ($quote->quote->billables as $billable) {
                $billables[] = [
                    'action' => $billable->action->value,
                    'product' => $billable->product,
                    'quantity' => $billable->quantity
                ];
            }
        }
        return $billables;
    }

    protected static function getDomainName(Domain | string $domain): string
    {
        if (is_string($domain)) {
            $domainName = App::toPunyCode($domain);
            return $domainName . Config::getPseudoTld(MetadataService::getTld($domainName));
        }
        $tld = $domain->tldPunyCode ?? $domain->tld;
        return $domain->domainName() . Config::getPseudoTld($tld);
    }
}

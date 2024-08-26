<?php

namespace RealtimeRegister\Actions\Domains;

use Illuminate\Database\Capsule\Manager as Capsule;
use RealtimeRegister\App;
use RealtimeRegister\Models\RealtimeRegister\ContactMapping;
use RealtimeRegister\Models\Whmcs\Configuration;
use RealtimeRegister\Models\Whmcs\Domain;
use RealtimeRegister\Models\Whmcs\Orders;
use RealtimeRegister\Models\Whmcs\Registrars;
use RealtimeRegister\Services\ContactService;
use SandwaveIo\RealtimeRegister\Domain\TLDMetaData;

trait DomainTrait
{
    protected static array $CONTACT_ROLES = [
        "TECH" => "techContacts",
        "ADMIN" => "adminContacts",
        "BILLING" => "billingContacts"
    ];

    protected function getOrCreateContact(int $clientId, int $contactId, string $role, bool $organizationAllowed)
    {
        // Check if we override the handle in the settings
        $handle = match ($role) {
            'ADMIN' => $this->app->registrarConfig()->get('handle'),
            'BILLING' => $this->app->registrarConfig()->get('handle_billing'),
            'TECH' => $this->app->registrarConfig()->get('handle_tech'),
            default => null,
        };

        if ($handle) {
            return $handle;
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
        $whmcsContact = App::localApi()->contact($clientId, $contactId);


        // Try and match the whmcs contact to a rtr contact
        // Should we even match the contact? yes / on exception only?

        // $contact = ContactService::findRemote();

        // If we do not find a match we create a new contact
        $rtrContact = ContactService::convertToRtrContact($whmcsContact, $organizationAllowed);
        $handle = uniqid(App::registrarConfig()->contactHandlePrefix() ?: '');

        App::client()->contacts->create(
            customer: App::registrarConfig()->customerHandle(),
            handle: $handle,
            name: $rtrContact->get('name'),
            addressLine: $rtrContact->get('addressLine'),
            postalCode: $rtrContact->get('postalCode'),
            city: $rtrContact->get('city'),
            country: $rtrContact->get('country'),
            email: $rtrContact->get('email'),
            voice: $rtrContact->get('voice'),
            organization: $rtrContact->get('organization'),
            state: $rtrContact->get('state')
        );

        ContactService::addContactMapping($clientId, $contactId, $handle, $organizationAllowed);
        return $handle;
    }

    protected function getDomainNameservers(array $params, string $type = 'register'): array
    {
        if ($type === 'transfer' && self::transferKeepNameservers()) {
            return array_merge($params, ['ns1' => '', 'ns2' => '', 'ns3' => '', 'ns4' => '', 'ns5' => '']);
        }

        $whmcsDomain = Domain::query()->find($params['domainid']);
        if (!empty($whmcsDomain['orderid'])) {
            $order = Orders::query()->find($whmcsDomain['orderid']);

            if ($order['nameservers']) {
                foreach (explode(',', $order['nameservers']) as $key => $nameserver) {
                    $params['ns' . ($key + 1)] = $nameserver;
                }
                return $params;
            }
        }

        return array_merge(
            $params,
            [
                'ns1' => Configuration::query()->where('setting', 'DefaultNameserver1')->value('value'),
                'ns2' => Configuration::query()->where('setting', 'DefaultNameserver2')->value('value'),
                'ns3' => Configuration::query()->where('setting', 'DefaultNameserver3')->value('value'),
                'ns4' => Configuration::query()->where('setting', 'DefaultNameserver4')->value('value'),
                'ns5' => Configuration::query()->where('setting', 'DefaultNameserver5')->value('value')
            ]
        );
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

    public static function transferKeepNameservers(): bool
    {
        $config = Registrars::getRegistrarConfig(['transfer_keep_nameservers']);

        return isset($config['transfer_keep_nameservers']) && $config['transfer_keep_nameservers'] === 'on';
    }
}

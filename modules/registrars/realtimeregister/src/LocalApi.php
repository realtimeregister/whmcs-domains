<?php

namespace RealtimeRegisterDomains;

use Exceptions\InternalApiException;
use Illuminate\Support\Collection;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Entities\WhmcsContact;
use RealtimeRegisterDomains\Services\LogService;

class LocalApi
{
    /**
     * @throws \Exception
     */
    public static function getContactDetails(int $clientId, int $contactId): array
    {
        $startNumber = 0;
        while (true) {
            $results = self::getLocalApi('GetContacts', ['userid' => $clientId, 'limitstart' => $startNumber]);

            foreach ($results['contacts']['contact'] as $contact) {
                if ($contact['id'] == $contactId) {
                    return $contact;
                }
            }

            if ((int)$results['numreturned'] + $startNumber >= (int)$results['totalresults']) {
                break;
            }

            $startNumber += (int)$results['numreturned'];
        }

        throw new \Exception(sprintf('Contact with ID %s not found', $contactId));
    }

    /**
     * @param  array $filters
     * @return Collection<DataObject>
     */
    public function domains(array $filters = []): Collection
    {
        $results = self::getLocalApi('GetClientsDomains', array_filter($filters));

        return collect($results['domains'] ?? [])->map(fn($domain) => new DataObject($domain[0]));
    }

    public function domain(int $clientId = null, int $domainId = null, string $domain = null): ?DataObject
    {
        return $this->domains(
            [
                'clientid' => $clientId,
                'domainid' => $domainId,
                'domain' => $domain
            ]
        )->first();
    }

    /**
     * @param  array $filters
     * @return Collection<DataObject>
     */
    public function orders(array $filters = []): Collection
    {
        $results = self::getLocalApi('GetOrders', array_filter($filters));

        return collect($results['orders'] ?? [])->map(fn($order) => new DataObject($order[0]));
    }

    public function order(
        int $id = null,
        int $clientId = null,
        int $requestorId = null,
        string $status = null
    ): ?DataObject {
        return $this->orders(
            [
                'id' => $id,
                'clientid' => $clientId,
                'requestor_id' => $requestorId,
                'status' => $status
            ]
        )->first();
    }

    public function getDomainOrder(int $domainId, int $clientId = null): ?DataObject
    {
        $domain = $this->domain($clientId, $domainId);
        if ($domain == null) {
            return null;
        }
        return $this->order($domain['orderid'], $clientId);
    }

    public static function getClient(int $clientId): array
    {
        return  self::getLocalApi('GetClientsDetails', ['clientid' => $clientId]);
    }

    public function getContact(int $clientId, int $contactId): ?DataObject
    {
        $start = 0;

        do {
            $results = self::getLocalApi('GetContacts', ['userid' => $clientId, 'startnumber' => $start]);

            $contact = collect($results['contacts']['contact'])->where('id', $contactId);

            if ($contact->isNotEmpty()) {
                /** @var DataObject $result */
                $result = $contact->first();

                $result['phonenumberformatted'] = WhmcsContact::formatE164a($result['phonenumber'], $result['country']);

                return new DataObject($result);
            }

            $start += (int)$results['numreturned'];
        } while ($start < (int)$results['totalresults']);

        $results = self::getLocalApi('GetClientsDetails', ['clientid' => $clientId])['client'];
        $results['phonenumberformatted'] = WhmcsContact::formatE164a($results['phonenumber'], $results['country']);
        return new DataObject($results);
    }

    public function getContactById(int $contactId): ?DataObject
    {
        return new DataObject(self::getLocalApi('GetContactDetails', ['id' => $contactId])['contacts'][0]);
    }

    public function getTldPricing()
    {
        $currentUser = new \WHMCS\Authentication\CurrentUser();
        $qry = [];
        if ($currentUser) {
            $qry['clientId'] = (string)$currentUser->user()->id;
        } else {
            $qry['currencyId'] = 'USD';
        }
        $results = self::getLocalApi('GetTldPricing', $qry);

        if ($results) {
            return $results['pricing'];
        }

        return null;
    }

    public function getCurrencies()
    {
        return self::getLocalApi('GetCurrencies', []);
    }

    public function addClient($postData, $admin)
    {
        return self::getLocalApi('AddClient', $postData, $admin);
    }

    public function addContact($postData, $admin)
    {
        return self::getLocalApi('AddContact', $postData, $admin);
    }

    private static function getLocalApi($function, $postData, $user = null)
    {
        $results = localAPI($function, $postData, $user);
        if ($results['result'] === 'error') {
            LogService::logError($results['message']);
            throw new InternalApiException($results['message']);
        }
        return $results;
    }
}

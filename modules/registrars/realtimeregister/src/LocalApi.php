<?php

namespace RealtimeRegister;

use Illuminate\Support\Collection;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Entities\WhmcsContact;

class LocalApi
{
    /**
     * @throws \Exception
     */
    public static function getContactDetails(int $clientId, int $contactId): array
    {
        $startNumber = 0;
        while (true) {
            $results = localAPI('GetContacts', ['userid' => $clientId, 'limitstart' => $startNumber]);

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
        $data = localApi('GetClientsDomains', array_filter($filters));


        return collect($data['domains'] ?? [])->map(fn($domain) => new DataObject($domain[0]));
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
        $data = localApi('GetOrders', array_filter($filters));

        return collect($data['orders'] ?? [])->map(fn($order) => new DataObject($order[0]));
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

    public static function getClient(int $clientId): DataObject
    {
        return new DataObject(localAPI('GetClientDetails', ['id' => $clientId])['client']);
    }

    public function getContact(int $clientId, int $contactId): ?DataObject
    {
        $start = 0;

        do {
            $data = localAPI('GetContacts', ['userid' => $clientId, 'startnumber' => $start]);

            $contact = collect($data['contacts']['contact'])->where('id', $contactId);

            if ($contact->isNotEmpty()) {
                /** @var DataObject $result */
                $result = $contact->first();

                $result['phonenumberformatted'] = WhmcsContact::formatE164a($result['phonenumber']);

                return new DataObject($result);
            }

            $start += (int)$data['numreturned'];
        } while ($start < (int)$data['totalresults']);

        $data = localAPI('GetClientsDetails', ['clientid' => $clientId])['client'];

        $result['phonenumberformatted'] = WhmcsContact::formatE164a($data['phonenumber']);
        return new DataObject($data);
    }

    public function getContactById(int $contactId): ?DataObject
    {
        return new DataObject(localAPI('GetContactDetails', ['id' => $contactId])['contacts'][0]);
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
        $result = localAPI('GetTldPricing', $qry);

        if ($result) {
            return $result['pricing'];
        }

        return null;
    }
}

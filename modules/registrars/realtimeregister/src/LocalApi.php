<?php

namespace RealtimeRegister;

use Illuminate\Support\Collection;
use RealtimeRegister\Entities\DataObject;

class LocalApi
{
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

    public function client(int $clientId): DataObject
    {
        return new DataObject(localAPI('GetClientDetails', ['id' => $clientId])['client']);
    }

    public function contact(int $clientId, int $contactId): ?DataObject
    {
        $start = 0;

        do {
            $data = localAPI('GetContacts', ['userid' => $clientId, 'startnumber' => $start]);

            $contact = collect($data['contacts']['contact'])->where('id', $contactId)->mapInto(DataObject::class);

            if ($contact->isNotEmpty()) {
                return $contact->first();
            }

            $start += (int)$data['numreturned'];
        } while ($start < (int)$data['totalresults']);



        return new DataObject(localAPI('GetClientsDetails', ['clientid' => $clientId])['client']);
    }

    public function createContact(): void
    {
        localAPI(
            'AddContact',
            [
                'firstname' => ''
            ]
        );
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
    }
}

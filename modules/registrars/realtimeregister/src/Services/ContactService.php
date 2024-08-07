<?php

namespace RealtimeRegister\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use RealtimeRegister\App;
use RealtimeRegister\Entities\Contact;
use RealtimeRegister\Models\ContactMapping;
use SandwaveIo\RealtimeRegister\Domain\Contact as RTRContact;

class ContactService
{
    public function findRemote(Contact $contact, bool $organizationAllowed)
    {
        $params = Arr::only($contact->toArray(), ['organization', 'name', 'email', 'country']);

        if (!$organizationAllowed) {
            unset($params['organization']);
            $params['organization:null'] = '';
        }

        $params = array_merge($params, [
            'order' => '-createdDate',
            'export' => true
        ]);

        dd($params);

        $result = App::client()->contacts->list(
            customer: App::registrarConfig()->customerHandle(),
            parameters: $params
        );

        dd($result);
    }

    public function findByContactId($contactId)
    {
        // Find a local contact by its id
    }

    public function findLocal(RTRContact $contact)
    {
        // Search in contact mappings

        // Search with data
    }

    /**
     * @param string|int $userId
     * @param string|int $contactId
     * @return Collection<ContactMapping>
     */
    public function fetchMappingByContactId(string|int $userId, string|int $contactId): Collection
    {
        return ContactMapping::query()->where('userid', $userId)->where('contactid', $contactId)->get();
    }

    public function fetchMappingByHandle(string $handle): ?ContactMapping
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ContactMapping::query()->where('handle', $handle)->first();
    }

    public function handleHasMapping(string $handle): bool
    {
        return ContactMapping::query()->where('handle', $handle)->exists();
    }
}

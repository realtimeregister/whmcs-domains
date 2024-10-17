<?php

namespace RealtimeRegisterDomains\Actions\Contacts;

use Illuminate\Support\Arr;
use RealtimeRegister\Domain\Contact;
use RealtimeRegister\Domain\DomainContact;
use RealtimeRegister\Domain\DomainDetails;
use RealtimeRegister\Domain\Enum\DomainContactRoleEnum;

trait ContactDetailsTrait
{
    protected function fetchContact(DomainDetails $domain, string $role): ?array
    {
        DomainContactRoleEnum::validate($role);

        if ($role === DomainContactRoleEnum::ROLE_REGISTRANT) {
            return $this->mapContact($this->contactInfo($domain->registrant));
        }

        if (!$domain->contacts) {
            return null;
        }

        $contact = Arr::first($domain->contacts->entities, fn(DomainContact $contact) => $contact->role === $role);

        if (!$contact) {
            return null;
        }

        return $this->mapContact($this->contactInfo($contact->handle));
    }

    protected function mapContact(Contact $contact): array
    {
        return [
            'Company Name' => $contact->organization,
            'Contact Name' => $contact->name,
            'Address 1' => $contact->addressLine[0] ?? null,
            'Address 2' => implode(', ', array_slice($contact->addressLine, 1)),
            'Postcode' => $contact->postalCode,
            'City' => $contact->city,
            'State' => $contact->state,
            'Country' => $contact->country,
            'Email' => $contact->email,
            'Phone' => $contact->voice,
            'Fax' => $contact->fax,
        ];
    }
}

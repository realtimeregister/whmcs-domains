<?php

namespace RealtimeRegister\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RealtimeRegister\App;
use RealtimeRegister\Enums\ContactType;
use RealtimeRegister\Models\Domain;
use RealtimeRegister\Request;
use RealtimeRegister\Services\ContactService;
use RealtimeRegister\Models\Contact as ContactModel;
use SandwaveIo\RealtimeRegister\Domain\Contact;
use SandwaveIo\RealtimeRegister\Domain\DomainContact;
use SandwaveIo\RealtimeRegister\Domain\DomainDetails;
use SandwaveIo\RealtimeRegister\Domain\Enum\DomainContactRoleEnum;
use WHMCS\View\Template\AssetUtil;
use function RealtimeRegister\dd;
use function RealtimeRegister\dump;

class SaveContactDetails extends Action
{
    protected array $roles = [
        ContactModel::ROLE_REGISTRANT => DomainContactRoleEnum::ROLE_REGISTRANT,
        ContactModel::ROLE_ADMIN => DomainContactRoleEnum::ROLE_ADMIN,
        ContactModel::ROLE_BILLING => DomainContactRoleEnum::ROLE_BILLING,
        ContactModel::ROLE_TECH => DomainContactRoleEnum::ROLE_TECH
    ];

    public function __invoke(Request $request)
    {
        $domain = $this->domainInfo($request);

        $whmcsDomain = Domain::query()->where('id', $request->get('domainid'))->first();

        $userId = (int)$request->get('userid');

        $metadata = $this->tldInfo($request)->metadata;

        dd($request->input());

        foreach ($this->roles as $whmcsRole => $role) {

            // Role is not present
            if (!$request->input('wc.' . $whmcsRole)) {
                continue;
            }

            $key = $role === DomainContactRoleEnum::ROLE_REGISTRANT ? 'registrant' : sprintf('%sContacts', strtolower($role));

            /** @var bool $organizationAllowed */
            $organizationAllowed = $metadata->{$key}->organizationAllowed;

            $currentHandle = $domain->registrant;

            if ($role !== DomainContactRoleEnum::ROLE_REGISTRANT) {
                $currentHandle = Arr::first($domain->contacts->entities, fn(DomainContact $contact) => $contact->role === $role)?->handle;
            }

            dump($role . ' - ' . $key . ' - ' . ($organizationAllowed ? 'Org allowed' : ' No org allowed') . ' - ' . $currentHandle);
            if ($request->input('wc.' . $whmcsRole) === 'custom')  {
                if (App::contacts()->handleHasMapping($currentHandle)) {

                    DB::transaction(function() use($request) {

                        App::localApi()->createContact();

                    });

                    // First create a new contact in whmcs

                    // Then create it at the registry

                    App::client()->contacts->create(
                        customer: App::registrarConfig()->customerHandle(),
                        handle: $newHandle = uniqid(App::registrarConfig()->contactHandlePrefix()),
                        name: 'name',
                        addressLine: ['line1'],
                        postalCode: 'test',
                        city: 'city',
                        country: 'nl',
                        email: 'mail@example.com',
                        voice: 'phoneNumber',
                        brand: 'nl',
                        organization: 'test',
                        state: 'overijsel',
                        fax: 'faxNumber'
                    );

                    dump('Has a handle');
                    // Create a new contact and associate that with the domain
                } else {
                    dump('No handle mapping');
                    // Update the handle at rtr
                }



                continue;
            }

            // If contact is selected we fetch the contact details, store it and map it

            if ($request->input('wc.' . $whmcsRole) === 'contact') {
                // Contact/Client was selected

                $selected = $request->input('sel.' . $whmcsRole);

                if (str_starts_with($selected, 'u')) {
                    // Update the contact with the user information, if there is a mapping already for the current handle we create a new one
                } else {
                    // Check if the contact
                }

                dump($selected);

                continue;
            }



        }

        dd($request->input(), $metadata);

    }

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
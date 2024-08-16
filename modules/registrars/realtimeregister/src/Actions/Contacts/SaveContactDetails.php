<?php

namespace RealtimeRegister\Actions\Contacts;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Models\Whmcs\Contact as ContactModel;
use RealtimeRegister\Models\Whmcs\Domain;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\DomainContact;
use SandwaveIo\RealtimeRegister\Domain\Enum\DomainContactRoleEnum;
use WHMCS\View\Template\AssetUtil;

class SaveContactDetails extends Action
{
    use ContactDetailsTrait;

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

        foreach ($this->roles as $whmcsRole => $role) {
            // Role is not present
            if (!$request->input('wc.' . $whmcsRole)) {
                continue;
            }

            $key = $role === DomainContactRoleEnum::ROLE_REGISTRANT
                ? 'registrant' : sprintf('%sContacts', strtolower($role));

            /** @var bool $organizationAllowed */
            $organizationAllowed = $metadata->{$key}->organizationAllowed;

            $currentHandle = $domain->registrant;

            if ($role !== DomainContactRoleEnum::ROLE_REGISTRANT) {
                $currentHandle = Arr::first(
                    $domain->contacts->entities,
                    fn(DomainContact $contact) => $contact->role === $role
                )?->handle;
            }

            // @todo: This is still a work in progress.

            dump(
                $role . ' - ' . $key . ' - '
                . ($organizationAllowed ? 'Org allowed' : ' No org allowed') . ' - ' . $currentHandle
            );
            if ($request->input('wc.' . $whmcsRole) === 'custom') {
                if (App::contacts()->handleHasMapping($currentHandle)) {
                    DB::transaction(function () use ($request) {
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
                    // Update the contact with the user information, if there is a mapping already for the current
                    // handle we create a new one
                } else {
                    // Check if the contact
                }

                dump($selected);

                continue;
            }
        }

        dd($request->input(), $metadata);
    }
}

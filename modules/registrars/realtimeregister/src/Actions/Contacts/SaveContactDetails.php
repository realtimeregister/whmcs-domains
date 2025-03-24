<?php

namespace RealtimeRegisterDomains\Actions\Contacts;

use Illuminate\Support\Arr;
use RealtimeRegister\Domain\Contact;
use RealtimeRegister\Domain\DomainContact;
use RealtimeRegister\Domain\DomainContactCollection;
use RealtimeRegister\Domain\Enum\DomainContactRoleEnum;
use RealtimeRegister\Domain\TLDInfo;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegister\Exceptions\UnauthorizedException;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\Actions\Domains\DomainContactTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Exceptions\DomainNotFoundException;
use RealtimeRegisterDomains\Models\RealtimeRegister\ContactMapping;
use RealtimeRegisterDomains\Models\Whmcs\Contact as ContactModel;
use RealtimeRegisterDomains\Models\Whmcs\Domain;
use RealtimeRegisterDomains\PunyCode;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\ContactService;
use WHMCS\View\Template\AssetUtil;

class SaveContactDetails extends Action
{
    use ContactDetailsTrait;
    use DomainContactTrait;

    protected array $roles = [
        ContactModel::ROLE_REGISTRANT => DomainContactRoleEnum::ROLE_REGISTRANT,
        ContactModel::ROLE_ADMIN => DomainContactRoleEnum::ROLE_ADMIN,
        ContactModel::ROLE_BILLING => DomainContactRoleEnum::ROLE_BILLING,
        ContactModel::ROLE_TECH => DomainContactRoleEnum::ROLE_TECH
    ];

    private array $params;

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request)
    {
        $this->params = $request->params;

        try {
            $domain = $this->domainInfo($request);

            $whmcsDomain = Domain::query()->where('id', $request->get('domainid'))->first();
            $clientId = $whmcsDomain['userid'];

            $tldInfo = $this->tldInfo($request);
            $metadata = $tldInfo->metadata;

            $newDomainContacts = [];
            $createdContacts = [];
            $updatedContacts = [];
            $deleteContacts = [];


            foreach ($this->roles as $whmcsRole => $role) {
                // Role is not present
                if (!$request->input('wc.' . $whmcsRole)) {
                    continue;
                }

                $key = $role === DomainContactRoleEnum::ROLE_REGISTRANT
                    ? 'registrant' : sprintf('%sContacts', strtolower($role));

                /** @var bool $organizationAllowed */
                $organizationAllowed = $metadata->{$key}->organizationAllowed;

                if ($role !== DomainContactRoleEnum::ROLE_REGISTRANT) {
                    $currentHandle = Arr::first(
                        $domain->contacts->entities,
                        fn(DomainContact $contact) => $contact->role === $role
                    )?->handle;
                } else {
                    $currentHandle = $domain->registrant;
                }

                if ($request->input('wc.' . $whmcsRole) === 'contact') {
                    // A contact (or client) was selected
                    $selected = $_POST['sel'][$whmcsRole];
                    $contactId = (int)str_starts_with($selected, 'c') ? ltrim($selected, 'c') : 0;
                    $properties = $this->getProperties($tldInfo);
                    $newHandle = ContactService::getOrCreateContact(
                        $clientId,
                        $contactId,
                        $role,
                        $organizationAllowed,
                        $tldInfo,
                        $properties
                    );

                    if ($currentHandle != $newHandle) {
                        $newDomainContacts[$role] = $newHandle;

                        if (!ContactMapping::where('handle', $currentHandle)) {
                            // No mapping for current handle, try to delete the current contact
                            $deleteContacts[] = $currentHandle;
                        }
                    }
                } else {
                    $newContact = ContactService::convertWhmcsDomainContactToRtrContact(
                        $request->get('contactdetails')[$whmcsRole],
                        $organizationAllowed
                    );

                    if (ContactMapping::query()->where('handle', '=', $currentHandle)->first()) {
                        /*
                         * There is a mapping for the current contact in this role. This means that a custom set of
                         * contact details is specified for the first time. We create a new contact in this case, which
                         * won't be mapped, because there is no corresponding contact or client.
                         */

                        $properties = $this->getProperties($tldInfo);
                        $newDomainContacts[$role] = ContactService::contactCreate(
                            rtrContact: new DataObject($newContact),
                            tldInfo: $tldInfo,
                            properties: $properties
                        );

                        $createdContacts[] = $newDomainContacts[$role];
                    } else {
                        /**
                         * No mapping, this means that a custom set of contact details was already associated with the
                         * domain. We can safely update the contact.
                         */
                        $currentContact = $this->contactInfo($currentHandle);

                        $diff = $this->getRtrContactDiff(
                            currentContact: $currentContact,
                            newContact: $newContact
                        );
                        if ($diff) {
                            $updatedContacts[$currentHandle] = $diff;
                        }
                    }
                }
            }

            if ($newDomainContacts) {
                $domainUpdate = [];

                if (array_key_exists(DomainContactRoleEnum::ROLE_REGISTRANT, $newDomainContacts)) {
                    $domainUpdate['registrant'] = $newDomainContacts[DomainContactRoleEnum::ROLE_REGISTRANT];
                    unset($newDomainContacts[DomainContactRoleEnum::ROLE_REGISTRANT]);
                }

                if ($newDomainContacts) {
                    $domainHandles = [];

                    foreach ($domain->contacts as $contact) {
                        $domainHandles[$contact->role] = $contact->handle;
                    }

                    $newDomainContacts = array_merge($domainHandles, $newDomainContacts);

                    $contacts = [];
                    foreach ($newDomainContacts as $role => $handle) {
                        $contacts[] = ['role' => $role, 'handle' => $handle];
                    }
                    $domainUpdate['contacts'] = DomainContactCollection::fromArray($contacts);
                }

                try {
                    $this->domainUpdate($domainUpdate);
                } catch (\Exception $ex) {
                    if ($createdContacts) {
                        foreach ($createdContacts as $contact) {
                            try {
                                App::client()->contacts->delete(App::registrarConfig()->customerHandle(), $contact);
                            } catch (\Exception) {
                                // ignore
                            }
                        }
                    }
                    throw $ex;
                }
            }
            // Update contacts, if needed
            if ($updatedContacts) {
                foreach ($updatedContacts as $handle => $diff) {
                    $diff['handle'] = $handle;
                    $diff['customer'] = App::registrarConfig()->customerHandle();
                    App::client()->contacts->update(...$diff);
                }
            }

            if ($deleteContacts) {
                foreach (array_unique($deleteContacts) as $handle) {
                    try {
                        App::client()->contacts->delete(App::registrarConfig()->customerHandle(), $handle);
                    } catch (\Exception) {
                        // ignore
                    }
                }
            }
        } catch (BadRequestException | UnauthorizedException $exception) {
            throw new DomainNotFoundException($exception);
        }
    }

    public function getRtrContactDiff(Contact $currentContact, array $newContact): array
    {
        $diff = [];

        foreach (['name', 'addressLine', 'postalCode', 'city', 'country', 'email', 'voice'] as $field) {
            if ($currentContact->{$field} != $newContact[$field]) {
                $diff[$field] = $newContact[$field];
            }
        }

        foreach (['organization', 'state', 'fax'] as $field) {
            if ($currentContact->{$field} && !$newContact[$field]) {
                $diff[$field] = '';
            } elseif ($currentContact->{$field} != $newContact[$field] && $newContact[$field]) {
                $diff[$field] = $newContact[$field];
            }
        }

        return $diff;
    }

    public function getProperties(TLDInfo $tldInfo): array
    {
        return [
            'registry' => $tldInfo->provider,
            'properties' => self::getNewProperties($this->params['additionalfields'], $tldInfo->metadata)
        ];
    }
    public function domainUpdate($domain = []): void
    {
        if ($domain['privacyProtect']) {
            $domain = $domain + [
                    'billables' => [
                        0 => [
                            'product' => 'domain_' . $this->params['tld'],
                            'action' => 'PRIVACY_PROTECT',
                            'quantity' => "1",
                        ],
                    ],
                ];
        }

        $domainName = self::getDomainName($this->params['original']['domainname']);

        $params = $domain;
        $params['domainName'] = $domainName;
        App::client()->domains->update(...$params);

        if (!empty($this->domainInfo[$domainName])) {
            $this->domainInfo[$domainName] = array_merge($this->domainInfo[$domainName], $domain);
        }
    }
}

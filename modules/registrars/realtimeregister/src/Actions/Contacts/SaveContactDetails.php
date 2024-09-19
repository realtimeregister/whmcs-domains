<?php

namespace RealtimeRegister\Actions\Contacts;

use Illuminate\Support\Arr;
use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Exceptions\DomainNotFoundException;
use RealtimeRegister\Models\RealtimeRegister\ContactMapping;
use RealtimeRegister\Models\Whmcs\Contact as ContactModel;
use RealtimeRegister\Models\Whmcs\Domain;
use RealtimeRegister\Request;
use Realtimeregister\Services\Config\Config;
use Realtimeregister\Services\ContactService;
use SandwaveIo\RealtimeRegister\Domain\Contact;
use SandwaveIo\RealtimeRegister\Domain\DomainContact;
use SandwaveIo\RealtimeRegister\Domain\DomainContactCollection;
use SandwaveIo\RealtimeRegister\Domain\Enum\DomainContactRoleEnum;
use SandwaveIo\RealtimeRegister\Domain\TLDInfo;
use SandwaveIo\RealtimeRegister\Exceptions\BadRequestException;
use SandwaveIo\RealtimeRegister\Exceptions\UnauthorizedException;
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

                    if ($role == DomainContactRoleEnum::ROLE_REGISTRANT) {
                        $properties = $this->getProperties($tldInfo);
                        $newHandle = $this->getOrCreateRegistrantContact(
                            $clientId,
                            $contactId,
                            $organizationAllowed,
                            $tldInfo,
                            $properties
                        );
                    } else {
                        try {
                            $newHandle = $this->getOrCreateDomainContact(
                                clientId: $clientId,
                                contactId: $contactId,
                                role: $role,
                                tldInfo: $tldInfo,
                                organizationAllowed: $organizationAllowed
                            );
                        } catch (\Exception $exception) {
                            dd($exception);
                        }
                    }

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

                    if (ContactMapping::where('handle', $currentHandle)->first()) {
                        /*
                         * There is a mapping for the current contact in this role. This means that a custom set of
                         * contact details is specified for the first time. We create a new contact in this case, which
                         * won't be mapped, because there is no corresponding contact or client.
                         */
                        $properties = $role == DomainContactRoleEnum::ROLE_REGISTRANT ? $this->getProperties($tldInfo)
                            : null;
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

    /**
     * @throws \Exception
     */
    private function getOrCreateDomainContact(
        $clientId,
        int | string $contactId,
        mixed $role,
        $tldInfo,
        bool $organizationAllowed,
        ?array $properties = null
    ): string {
        $handle = ContactService::getConfiguredRoleHandle($role);

        if (!$handle) {
            $contact = ContactService::getRtrContactHandle(
                clientId: $clientId,
                contactId: $contactId,
                organizationAllowed: $organizationAllowed,
                properties: $properties
            );

            $handle = $contact->handle;
        }

        if ($handle) {
            return $handle;
        }

        $handle = ContactService::createRtrContactFromWhmcsContact(
            clientId: $clientId,
            contactId: $contactId,
            organizationAllowed: $organizationAllowed,
            tldInfo: $tldInfo,
            properties: $properties
        );
        ContactService::storeContactMapping(
            clientId: $clientId,
            contactId: $contactId,
            handle: $handle,
            organizationAllowed: $organizationAllowed
        );

        return $handle;
    }

    /**
     * @throws \Exception
     */
    public static function getOrCreateRegistrantContact(
        int $clientId,
        int $contactId,
        bool $organizationAllowed,
        TLDInfo $tldInfo,
        ?array $properties = null
    ): ?string {
        $handle = ContactService::getRtrContactHandle(
            clientId: $clientId,
            contactId: $contactId,
            organizationAllowed: $organizationAllowed,
            properties: $properties
        );

        if ($handle) {
            // Check if we need to add properties
            if ($properties['properties']) {
                $rtrContact = App::client()->contacts->get(App::registrarConfig()->customerHandle(), $handle);

                if (!$rtrContact->properties[$properties['registry']]) {
                    App::client()->contacts->addProperties(
                        customer: App::registrarConfig()->customerHandle(),
                        handle: $handle,
                        registry: $tldInfo->provider,
                        properties: $properties
                    );
                } elseif ($rtrContact->properties[$properties['registry']] != $properties['properties']) {
                    /**
                     * Contact mapping already exists, but properties don't match. Create new contact with specified
                     * properties, but don't store the mapping.
                     */
                    return ContactService::createRtrContactFromWhmcsContact(
                        clientId: $clientId,
                        contactId: $contactId,
                        organizationAllowed: $organizationAllowed,
                        tldInfo: $tldInfo,
                        properties: $properties
                    );
                }
            }
            return $handle;
        }

        $handle = ContactService::createRtrContactFromWhmcsContact(
            clientId: $clientId,
            contactId: $contactId,
            organizationAllowed: $organizationAllowed,
            tldInfo: $tldInfo,
            properties: $properties
        );
        ContactService::storeContactMapping(
            clientId: $clientId,
            contactId: $contactId,
            handle: $handle,
            organizationAllowed: $organizationAllowed
        );

        return $handle;
    }

    public function getProperties(TLDInfo $tldInfo): array
    {
        $properties = ['registry' => $tldInfo->provider, 'properties' => []];

        foreach ($this->params['additionalfields'] as $property => $value) {
            if ($property == 'languageCode' || !isset($tld_properties->{$property})) {
                continue;
            }
            if ($this->propertyIsBool($tld_properties[$property])) {
                $value = $this->getPropertyBoolValue($tld_properties->{$property}['values'], $value == 'on');
            }
            if (!empty($value) && array_key_exists($property, $tld_properties)) {
                $properties['properties'][$property] = $value;
            }
        }
        return $properties;
    }

    private function propertyIsBool($property): bool
    {
        if (!isset($property['values'])) {
            return false;
        } elseif (count($property['values']) == 1 && array_keys($property['values'])[0] == 'true') {
            return true;
        } elseif (count($property['values']) == 2) {
            $sorted = $property['values'];
            sort($sorted);
            $sorted = strtolower(implode($sorted));
            if ($sorted == 'ny' || $sorted == 'falsetrue') {
                return true;
            }
        }
        return false;
    }

    private function getPropertyBoolValue($propertyValues, $bool)
    {
        $boolValues = $bool ? ['true', 'y'] : ['false', 'n'];
        $arr = array_filter(array_keys($propertyValues), function ($v) use ($boolValues) {
            return in_array(strtolower($v), $boolValues);
        });
        return empty($arr) ? '' : array_shift($arr);
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

        $domainName = $this->domainBuildName(false);
        $params = $domain;
        $params['domainName'] = $domainName;
        App::client()->domains->update(...$params);

        if (!empty($this->domainInfo[$domainName])) {
            $this->domainInfo[$domainName] = array_merge($this->domainInfo[$domainName], $domain);
        }
    }

    /**
     * Generate domain name
     *
     * @param bool $internal
     *   In case this is not internal use, use punycode version
     *
     * @return string
     */
    private function domainBuildName($internal = true)
    {
        $domainName = $this->params['original']['domainname'];
        if (!$internal) {
            $domainName = $this->params['original']['domain_punycode'];
        }
        if (Config::get('tldinfomapping.' . $this->params['tld']) === 'centralnic') {
            $domainName .= '.centralnic';
        }

        return $domainName;
    }
}

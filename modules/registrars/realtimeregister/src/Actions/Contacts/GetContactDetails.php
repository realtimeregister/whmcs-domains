<?php

namespace RealtimeRegister\Actions\Contacts;

use Illuminate\Support\Arr;
use RealtimeRegister\Actions\Action;
use RealtimeRegister\App;
use RealtimeRegister\Models\Whmcs\Contact as ContactModel;
use RealtimeRegister\Request;
use SandwaveIo\RealtimeRegister\Domain\DomainContact;
use SandwaveIo\RealtimeRegister\Domain\DomainDetails;
use SandwaveIo\RealtimeRegister\Domain\Enum\DomainContactRoleEnum;

class GetContactDetails extends Action
{
    use ContactDetailsTrait;

    public function __invoke(Request $request): array
    {
        $domain = $this->domainInfo($request);

        $handles = json_encode(
            [
            ContactModel::ROLE_REGISTRANT => $this->fetchId($domain, DomainContactRoleEnum::ROLE_REGISTRANT),
            ContactModel::ROLE_ADMIN => $this->fetchId($domain, DomainContactRoleEnum::ROLE_ADMIN),
            ContactModel::ROLE_TECH => $this->fetchId($domain, DomainContactRoleEnum::ROLE_TECH),
            ContactModel::ROLE_BILLING => $this->fetchId($domain, DomainContactRoleEnum::ROLE_BILLING)
            ]
        );

        App::assets()->prependHead("<script>let contact_ids = $handles;</script>");

        return array_filter(
            [
            ContactModel::ROLE_REGISTRANT => $this->fetchContact($domain, DomainContactRoleEnum::ROLE_REGISTRANT),
            ContactModel::ROLE_ADMIN => $this->fetchContact($domain, DomainContactRoleEnum::ROLE_ADMIN),
            ContactModel::ROLE_TECH => $this->fetchContact($domain, DomainContactRoleEnum::ROLE_TECH),
            ContactModel::ROLE_BILLING => $this->fetchContact($domain, DomainContactRoleEnum::ROLE_BILLING)
            ]
        );
    }

    protected function fetchId(DomainDetails $domain, string $role): ?string
    {
        $handle = $role === DomainContactRoleEnum::ROLE_REGISTRANT
            ? $domain->registrant
            : Arr::first($domain->contacts->entities, fn(DomainContact $contact) => $contact->role === $role)?->handle;

        if (!$handle) {
            return null;
        }

        $map = App::contacts()->fetchMappingByHandle($handle);

        if (!$map) {
            return null;
        }

        return !empty($map->contactid) ? sprintf("c%s", $map->contactid) : sprintf("u%s", $map->userid);
    }
}

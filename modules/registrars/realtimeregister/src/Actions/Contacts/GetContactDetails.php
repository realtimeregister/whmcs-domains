<?php

namespace RealtimeRegisterDomains\Actions\Contacts;

use Illuminate\Support\Arr;
use RealtimeRegisterDomains\Actions\Action;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Exceptions\DomainNotFoundException;
use RealtimeRegisterDomains\Models\Whmcs\Contact as ContactModel;
use RealtimeRegisterDomains\Request;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegister\Domain\DomainContact;
use RealtimeRegister\Domain\DomainDetails;
use RealtimeRegister\Domain\Enum\DomainContactRoleEnum;
use RealtimeRegister\Exceptions\BadRequestException;
use RealtimeRegister\Exceptions\UnauthorizedException;

class GetContactDetails extends Action
{
    use ContactDetailsTrait;

    public function __invoke(Request $request): array
    {
        try {
            $domain = $this->domainInfo($request);
        } catch (BadRequestException | DomainNotFoundException | UnauthorizedException $e) {
            LogService::logError($e);
            return ['error' => sprintf('Error with domaininformation lookup: %s', $e->getMessage())];
        }

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

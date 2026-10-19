<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegister\Domain\BillableCollection;
use RealtimeRegister\Domain\DomainContactCollection;
use RealtimeRegister\Domain\DomainQuote;
use RealtimeRegister\Domain\Enum\DomainContactRoleEnum;
use RealtimeRegister\Domain\TLDInfo;
use RealtimeRegisterDomains\Actions\Domains\DomainTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Models\RealtimeRegister\Cache;
use RealtimeRegisterDomains\Models\RealtimeRegister\ContactMapping;
use RealtimeRegisterDomains\Models\Whmcs\Domain;
use RealtimeRegisterDomains\Services\ContactService;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;

/**
 * WHMCS has no dedicated hook for "Transfer Ownership" of a domain, but it does write
 * "Moved Domain - From Client ID: <old> to Client ID: <new> - Domain ID: <id> - Client ID: <new>" to the
 * activity log, so we listen to LogActivity hook.
 *
 * When a domain of ours is moved, the registrant and contacts that belong to the previous owner are replaced
 * with the contact of the new owner.
 */
class DomainOwnershipTransfer extends Hook
{
    use DomainTrait;

    public const PATTERN = '/Moved Domain - From Client ID:\s*(?<old>\d+)\s+to Client ID:\s*(?<new>\d+)'
        . '\s+-\s+Domain ID:\s*(?<domain>\d+)/i';

    public function __invoke(DataObject $vars): void
    {
        if (!preg_match(self::PATTERN, (string)$vars->get('description'), $matches)) {
            return;
        }

        $domainId = (int)$matches['domain'];
        $oldUserId = (int)$matches['old'];
        $newUserId = (int)$matches['new'];

        $domain = Domain::query()->where('id', $domainId)->first();

        if (
            !$domain
            || $domain->registrar !== App::NAME
            || $domain->status !== 'Active'
            || $oldUserId === $newUserId
        ) {
            return;
        }

        try {
            $this->changeOwner($domain->domain, $oldUserId, $newUserId);
        } catch (\Throwable $exception) {
            LogService::logError($exception, sprintf('Unable to change the owner of %s', $domain->domain));
            logActivity(
                sprintf(
                    'Realtime Register: unable to update the contacts of %s to the new owner (User ID: %d), '
                    . 'please update the contact details manually: %s',
                    $domain->domain,
                    $newUserId,
                    $exception->getMessage()
                ),
                $newUserId
            );
        }
    }

    /**
     * @throws \Exception
     */
    private function changeOwner(string $whmcsDomainName, int $oldUserId, int $newUserId): void
    {
        $domainName = self::getDomainName($whmcsDomainName);
        $rtrDomain = App::client()->domains->get($domainName);
        $tldInfo = $this->tldInfo($whmcsDomainName);
        $metadata = $tldInfo->metadata;

        $update = [];

        // The registrant should be replaced when it isn't mapped to a contact of the new owner
        if ($this->mappedUserId($rtrDomain->registrant) !== $newUserId) {
            $properties = $this->registrantProperties($rtrDomain->registrant, $tldInfo->provider);

            $registrant = $this->newOwnerHandle(
                $newUserId,
                DomainContactRoleEnum::ROLE_REGISTRANT,
                $metadata->registrant->organizationAllowed,
                $tldInfo,
                $properties ? ['registry' => $tldInfo->provider, 'properties' => $properties] : null
            );

            if ($registrant !== $rtrDomain->registrant) {
                $update['registrant'] = $registrant;
            }
        }

        $contacts = [];
        $contactsChanged = false;
        foreach ($rtrDomain->contacts ?? [] as $contact) {
            $handle = $contact->handle;
            // Only replace contacts that belong to the previous owner, leave configured/custom handles alone
            if (!$this->handleOverride($contact->role) && $this->mappedUserId($handle) === $oldUserId) {
                $handle = $this->newOwnerHandle(
                    $newUserId,
                    $contact->role,
                    $metadata->{self::$CONTACT_ROLES[$contact->role]}->organizationAllowed,
                    $tldInfo
                );
                $contactsChanged = $contactsChanged || $handle !== $contact->handle;
            }
            $contacts[] = ['role' => $contact->role, 'handle' => $handle];
        }

        if ($contactsChanged) {
            $update['contacts'] = DomainContactCollection::fromArray($contacts);
        }

        if (!$update) {
            return;
        }

        $update['domainName'] = $domainName;

        // A registrant change can be a (paid) trade at some registries, accept the costs of the quote
        $quote = $this->updateDomain($update, true);
        $billables = $quote ? $this->buildBillables($quote) : [];
        if ($billables) {
            $update['billables'] = BillableCollection::fromArray($billables);
        }

        $this->updateDomain($update);

        logActivity(
            sprintf(
                'Realtime Register: updated the contacts of %s to the new owner (User ID: %d)%s',
                $whmcsDomainName,
                $newUserId,
                $billables ? ', costs: ' . $this->describeCosts($quote) : ''
            ),
            $newUserId
        );
    }

    private function describeCosts(DomainQuote $quote): string
    {
        return trim(sprintf('%s %s', $quote->quote->currency, number_format($quote->quote->total / 100, 2)));
    }

    protected function mappedUserId(string $handle): ?int
    {
        $userId = ContactMapping::query()->where('handle', $handle)->value('userid');

        return $userId === null ? null : (int)$userId;
    }

    protected function tldInfo(string $whmcsDomainName): TLDInfo
    {
        return (new MetadataService(App::toPunyCode($whmcsDomainName)))->getAll();
    }

    protected function registrantProperties(string $handle, string $provider): array
    {
        $contact = App::client()->contacts->get(App::registrarConfig()->customerHandle(), $handle);

        return ($contact->properties ?? [])[$provider] ?? [];
    }

    /**
     * @throws \Exception
     */
    protected function newOwnerHandle(
        int $userId,
        string $role,
        bool $organizationAllowed,
        TLDInfo $tldInfo,
        ?array $properties = null
    ): string {
        return ContactService::getOrCreateContact(
            clientId: $userId,
            contactId: 0,
            role: $role,
            organizationAllowed: $organizationAllowed,
            tldInfo: $tldInfo,
            properties: $properties
        );
    }

    protected function updateDomain(array $update, bool $isQuote = false): ?DomainQuote
    {
        $quote = App::client()->domains->update(...$update, isQuote: $isQuote);
        if (!$isQuote) {
            Cache::forget('domain-info:' . $update['domainName']);
        }

        return $quote;
    }
}

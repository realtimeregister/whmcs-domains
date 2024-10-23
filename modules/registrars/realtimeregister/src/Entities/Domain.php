<?php

namespace RealtimeRegisterDomains\Entities;

use RealtimeRegisterDomains\Enums\ContactType;

class Domain
{
    public function __construct(
        public readonly string $name,
        public readonly string $tld,
        public readonly array $nameservers = [],
        public readonly ?int $id = null,
        public readonly ?Contact $registrant = null,
        public readonly ?Contact $admin = null,
        public readonly ?Contact $tech = null,
        public readonly ?Contact $billing = null,
        public readonly ?string $idnLanguage = null,
        public readonly ?string $punyCode = null,
        public readonly ?string $namePunyCode = null,
        public readonly ?string $tldPunyCode = null,
        public readonly ?bool $isIdn = null,
        public readonly ?bool $isInGracePeriod = null,
        public readonly ?bool $isInRedemptionGracePeriod = null,
        public readonly array $contactProperties = [],
        public readonly bool $privacyProtect = false
    ) {
    }

    public function domainName(): string
    {
        return $this->punyCode ?? $this->name . '.' . $this->tld;
    }

    public static function fromWhmcs(array $params): static
    {
        return new static(
            name: $params['sld'],
            tld: $params['tld'],
            nameservers: array_filter(
                [
                $params['ns1'] ?? null,
                $params['ns2'] ?? null,
                $params['ns3'] ?? null,
                $params['ns4'] ?? null,
                $params['ns5'] ?? null,
                ]
            ),
            id: $params['id'] ?: ($params['domainid'] ?? null),
            registrant: Contact::fromWhmcs(ContactType::Registrant, $params),
            admin: Contact::fromWhmcs(ContactType::Admin, $params),
            tech: Contact::fromWhmcs(ContactType::Tech, $params),
            billing: Contact::fromWhmcs(ContactType::Billing, $params),
            idnLanguage: ($params['additionalfields'] ?? [])['languageCode'],
            punyCode: $params['domain_punycode'] ?? null,
            namePunyCode: $params['sld_punycode'] ?? null,
            tldPunyCode: $params['tld_punycode'] ?? null,
            isIdn: $params['is_idn'] ?? null,
            isInGracePeriod: $params['isInGracePeriod'] ?? null,
            isInRedemptionGracePeriod: $params['isInRedemptionGracePeriod'] ?? null,
            contactProperties: array_filter(
                $params['additionalfields'] ?? [],
                fn($key) => $key !== 'languageCode',
                ARRAY_FILTER_USE_KEY
            ),
            privacyProtect: $params['protectenable'] ?? $params['idprotection'] ?? false
        );
    }
}

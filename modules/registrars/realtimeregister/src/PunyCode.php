<?php

namespace RealtimeRegisterDomains;

use RealtimeRegisterDomains\Entities\Domain as DomainEntity;
use RealtimeRegisterDomains\Services\MetadataService;

trait PunyCode
{
    /**
     * @throws \Exception
     */
    public function checkForPunyCode(DomainEntity $domain): string
    {
        $tldInfo = (new MetadataService($domain->tld))->getAll();
        $metadata = $tldInfo->metadata;
        $domainName = $domain->domainName();

        if ($domain->domainName() !== $domain->punyCode) {
            // Check if we are allowed (& need) to use punycode
            if ($domain->isIdn && $metadata->domainSyntax->idnSupport) {
                if (
                    !array_key_exists(
                        strtoupper($domain->idnLanguage),
                        $metadata->domainSyntax->languageCodes->toArray()
                    )
                ) {
                    throw new \Exception(
                        sprintf(
                            'The language `%s` is not allowed for the %s tld.',
                            $domain->idnLanguage,
                            $domain->tld
                        )
                    );
                }
                $domainName = $domain->punyCode;
            } else {
                throw new \Exception(
                    sprintf('It is not possible to use IDN domainnames for the %s tld.', $domain->tld)
                );
            }
        }

        return $domainName;
    }
}

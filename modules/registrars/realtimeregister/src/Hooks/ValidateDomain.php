<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegister\Domain\TLDInfo;
use RealtimeRegister\Domain\TLDMetaData;
use RealtimeRegisterDomains\Actions\Domains\DomainTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;

class ValidateDomain extends Hook
{
    use CustomHandlesTrait;
    use DomainTrait;

    public function __invoke(DataObject $vars)
    {
        $errors = [];
        if (!empty($_SESSION['cart']['domains']) && self::hasNameServers()) {
            $nameservers = self::getNameServersFromCart();
            foreach ($_SESSION['cart']['domains'] as $domain) {
                try {
                    $metadata = (new MetadataService(App::toPunycode($domain['domain'])));
                    self::validateNameServers($metadata->getMetadata(), $domain['domain'], $nameservers, $errors);
                    $this->validateContacts($metadata->getAll(), $domain['domain'], $errors);
                } catch (\Exception $e) {
                    LogService::logError($e);
                }
            }
        }
        return $errors;
    }

    private static function hasNameServers(): bool
    {
        return [] !== array_filter([1,2,3,4,5], fn($item) => array_key_exists('ns' . $item, $_SESSION['cart']));
    }

    private static function validateNameServers(
        TLDMetaData $metadata,
        string $domain,
        array $nameservers,
        array &$errors
    ): void {
        if (count($nameservers) < $metadata->nameservers->min && $metadata->nameservers->required) {
            $errors[] = "'$domain'" . ' needs at least ' . $metadata->nameservers->min . ' nameservers';
        }
    }

    private function validateContacts(TLDInfo $tldInfo, string $domain, array &$errors): void
    {
        if ($_REQUEST['a'] !== 'checkout') {
            return;
        }
        $customHandles = $this->getCustomHandles();
        $error = $this->validateContact($tldInfo->metadata, "registrant", $domain);

        if ($error) {
            $errors[] = $error;
            return;
        }
        foreach (self::$CONTACT_ROLES as $name => $role) {
            if (
                $this->handleOverride($name) ||
                (array_key_exists($tldInfo->provider, $customHandles) &&
                array_key_exists($name, $customHandles[$tldInfo->provider]) &&
                $customHandles[$tldInfo->provider][$name] !== '')
            ) {
                continue;
            }

            $error = $this->validateContact($tldInfo->metadata, $role, $domain);
            if ($error) {
                $errors[] = $error;
                return;
            }
        }
    }

    private function validateContact(TLDMetaData $metadata, string $role, string $domain): ?string
    {
        if ($metadata->{$role}->organizationRequired && !$_REQUEST['companyname']) {
            return "Contact needs to be an organization for '" . $domain . "'";
        }
        return null;
    }

    private static function getNameServersFromCart()
    {
        $cart = $_SESSION['cart'];
        return array_filter([$cart['ns1'], $cart['ns2'], $cart['ns3'], $cart['ns4'], $cart['ns5']]);
    }
}

<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Services\MetadataService;
use TrueBV\Punycode;

class ValidateDomain extends Hook
{

    public function __invoke(DataObject $vars)
    {
        $errors = [];
        $nameservers = self::getNameServersFromCart();
        if (!empty($_SESSION['cart']['domains'])) {
            foreach ($_SESSION['cart']['domains'] as $domain) {
                $metadata = (new MetadataService((new Punycode())->encode($domain['domain'])))->getMetadata();
                if (count($nameservers) < $metadata->nameservers->min) {
                    $errors[] = $domain['domain'] . ' needs at least '. $metadata->nameservers->min . ' nameservers';
                }
            }
        }
        return $errors;
    }

    private static function getNameServersFromCart() {
        $cart = $_SESSION['cart'];
        return array_filter([$cart['ns1'], $cart['ns2'], $cart['ns3'], $cart['ns4'], $cart['ns5']]);
    }
}
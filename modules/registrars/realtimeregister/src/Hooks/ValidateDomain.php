<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;

class ValidateDomain extends Hook
{

    public function __invoke(DataObject $vars)
    {
        $errors = [];
        $nameservers = self::getNameServersFromCart();
        if (!empty($_SESSION['cart']['domains'])) {
            foreach ($_SESSION['cart']['domains'] as $domain) {
                try {
                    $metadata = (new MetadataService(App::toPunycode($domain['domain'])))->getMetadata();
                    if (count($nameservers) < $metadata->nameservers->min && $metadata->nameservers->required) {
                        $errors[] = $domain['domain']
                            . ' needs at least '
                            . $metadata->nameservers->min
                            . ' nameservers';
                    }
                } catch (\Exception $e) {
                    LogService::logError($e);
                }
            }
        }
        return $errors;
    }

    private static function getNameServersFromCart()
    {
        $cart = $_SESSION['cart'];
        return array_filter([$cart['ns1'], $cart['ns2'], $cart['ns3'], $cart['ns4'], $cart['ns5']]);
    }
}

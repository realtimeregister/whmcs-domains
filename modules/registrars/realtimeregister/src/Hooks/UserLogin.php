<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\LocalApi;
use RealtimeRegisterDomains\Services\ShoppingCartService;

class UserLogin extends Hook
{
    public function __invoke(DataObject $vars)
    {
        if (!empty($_SESSION['cart']['domains'])) {
            $whmcsCurrencies = [];
            foreach (App::localApi()->getCurrencies()['currencies']['currency'] as $c) {
                $whmcsCurrencies[$c['id']] = strtoupper($c['code']);
            }
            $client = LocalApi::getClient($_SESSION['uid']);
            if (!empty($client['currency']) && in_array($client['currency'], $whmcsCurrencies)) {
                ShoppingCartService::updateCartPremiumPrices($whmcsCurrencies[$client['currency']]);
            }
        }
    }
}

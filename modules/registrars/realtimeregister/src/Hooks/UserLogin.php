<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\Entities\DataObject;
use Realtimeregister\Services\ShoppingCartService;

class UserLogin extends Hook
{
    public function __invoke(DataObject $vars)
    {
        if (!empty($_SESSION['cart']['domains'])) {
            $whmcsCurrencies = [];
            foreach (localAPI('GetCurrencies', [])['currencies']['currency'] as $c) {
                $whmcsCurrencies[$c['id']] = strtoupper($c['code']);
            }
            $client = localAPI('GetClientDetails', ['clientid' => $_SESSION['uid']])['client'];
            if (!empty($client['currency']) && in_array($client['currency'], $whmcsCurrencies)) {
                ShoppingCartService::updateCartPremiumPrices($whmcsCurrencies[$client['currency']]);
            }
        }
    }
}

<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;
use TrueBV\Punycode;

class OrderDomainPricingOverride extends Hook
{
    public function __invoke(DataObject $vars)
    {
        if ($vars['type'] === 'register' && str_contains($_SERVER['REQUEST_URI'], '/admin/ordersadd.php')) {
            try {
                $domain = (new Punycode())->encode($vars['domain']);
                $res = App::client()->domains->check($domain);

                if ($res->price !== null) {
                    $metadata = (new MetadataService($domain))->getMetadata();
                    $price = ['firstPaymentAmount' => $res->price];
                    if ($metadata->premiumSupport === 'REGULAR') {
                        $price['recurringAmount'] = $res->price;
                    }
                    return $price;
                }
            } catch (\Exception $e) {
                LogService::logError($e);
            }
        }
        return null;
    }
}

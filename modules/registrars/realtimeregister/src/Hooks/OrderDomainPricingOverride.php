<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\PunyCode;
use RealtimeRegisterDomains\Services\MetadataService;

class OrderDomainPricingOverride extends Hook
{
    use PunyCode;

    public function __invoke(DataObject $vars): ?array
    {
        if ($vars['type'] === 'register' && str_contains($_SERVER['REQUEST_URI'], '/admin/ordersadd.php')) {
            $res = App::client()->domains->check($vars['domain']);

            if ($res->price !== null) {
                $metadata = (new MetadataService($vars['domain']))->getMetadata();
                $price = ['firstPaymentAmount' => $res->price];
                if ($metadata->premiumSupport === 'REGULAR') {
                    $price['recurringAmount'] = $res->price;
                }
                return $price;
            }
        }
        return null;
    }
}

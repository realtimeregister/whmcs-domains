<?php

// Perform any initialization required by the service's library.

use Whmcs\Domain\Domain;
use Whmcs\View\Menu\Item as MenuItem;

// Hooks incompatible with invokable hook
add_hook('ClientAreaPrimarySidebar', 1, function (MenuItem $primarySidebar) {
    (new WHMCS\Module\Addon\RealtimeregisterDns\Hooks\Client\ClientAreaPrimarySidebar())(
        $primarySidebar,
        Menu::context('domain')
    );
});

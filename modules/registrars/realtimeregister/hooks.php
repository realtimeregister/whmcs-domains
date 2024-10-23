<?php

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Hooks;
use Whmcs\View\Menu\Item as MenuItem;

require_once __DIR__ . '/vendor/autoload.php';

new \RealtimeRegisterDomains\Services\Language(); // Load our own language strings before anything else

App::boot();

App::hook(Hooks\PreRegistrarGetContactDetails::class);

App::hook(Hooks\AdminAreaPage::class, null, 10);
App::hook("AdminAreaHeadOutput", Hooks\CheckCredentials::class);
App::hook("AdminHomepage", Hooks\SyncExpiry::class, 2);
App::hook("AdminHomepage", Hooks\ImportDomains::class, 2);
App::hook("AdminHomepage", Hooks\AutoRenewStatus::class, 2);
App::hook(RealtimeRegisterDomains\Hooks\AdminClientDomainsTabFields::class);

App::hook('ClientAreaHeadOutput', Hooks\Adac::class, 10);
App::hook(Hooks\UserLogin::class);

App::hook(Hooks\Client\ClientAreaPage::class);
App::hook(Hooks\ContactEdit::class);
App::hook('ClientAreaHeadOutput', Hooks\Client\ClientAreaHeadOutput::class, 20);
App::hook(Hooks\Client\ClientAreaPageDomainDetails::class);

App::hook('AdminHomeWidgets', Hooks\Widgets\ActionsWidget::class);
App::hook('AdminHomeWidgets', Hooks\Widgets\DomainOverviewWidget::class);
App::hook('AdminHomeWidgets', Hooks\Widgets\BalanceWidget::class);
App::hook('AdminHomeWidgets', Hooks\Widgets\ErrorLogWidget::class, 40);
App::hook('AdminHomeWidgets', Hooks\Widgets\PromoWidget::class);

App::hook(Hooks\AdminHomepage::class);
App::hook('ClientAreaHeadOutput', Hooks\HeadAssets::class, 100);
App::hook('ClientAreaFooterOutput', Hooks\FooterAssets::class, 100);

App::hook('AdminAreaHeadOutput', Hooks\HeadAssets::class, 100);
App::hook('AdminAreaFooterOutput', Hooks\FooterAssets::class, 100);

App::hook('AdminAreaHeadOutput', Hooks\CustomHandles::class);

// Search for updates
App::hook('DailyCronJob', Hooks\Update\CheckForUpdates::class, 10);
App::hook('AdminAreaHeaderOutput', Hooks\Update\Banner::class, 10);
App::hook('AdminHomeWidgets', Hooks\Widgets\UpdateWidget::class, 40);
App::hook(Hooks\OrderDomainPricingOverride::class);
App::hook(Hooks\AdminClientDomainsTabFieldsSave::class);

App::hook('ShoppingCartValidateCheckout', Hooks\ValidateDomain::class);
App::hook('ShoppingCartValidateDomainsConfig', Hooks\ValidateDomain::class);

// Hooks incompatible with invokable hook
add_hook('ClientAreaPrimarySidebar', 1, function (MenuItem $primarySidebar) {
    (new Hooks\Client\ClientAreaPrimarySidebar())($primarySidebar, Menu::context('domain'));
});

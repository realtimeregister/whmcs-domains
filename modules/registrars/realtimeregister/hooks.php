<?php

use RealtimeRegister\App;
use RealtimeRegister\Hooks;

require_once __DIR__ . '/vendor/autoload.php';

new \RealtimeRegister\Services\Language(); // Load our own language strings before anything else

App::boot();

App::hook(Hooks\PreRegistrarGetContactDetails::class);

App::hook(Hooks\AdminAreaPage::class, null, 10);
App::hook("AdminAreaHeadOutput", Hooks\CheckCredentials::class);
App::hook("AdminHomepage", Hooks\SyncExpiry::class, 2);
App::hook("AdminHomepage", Hooks\ImportDomains::class, 2);
App::hook("AdminHomepage", Hooks\AutoRenewStatus::class, 2);
App::hook(RealtimeRegister\Hooks\AdminClientDomainsTabFields::class);

App::hook('ClientAreaHeadOutput', Hooks\Adac::class, 10);
App::hook('ShoppingCartValidateCheckout', Hooks\ShoppingCartValidate::class);
App::hook('ShoppingCartValidateDomainsConfig', Hooks\ShoppingCartValidate::class);
App::hook(Hooks\UserLogin::class);

App::hook(Hooks\ClientAreaPage::class);
App::hook(Hooks\ContactEdit::class);
App::hook('ClientAreaHeadOutput', Hooks\ClientAreaHeadOutput::class, 20);

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

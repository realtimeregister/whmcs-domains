<?php

use RealtimeRegister\App;
use RealtimeRegister\Hooks;

require_once __DIR__ . '/vendor/autoload.php';

App::boot();

// Utils
add_hook("AdminHomepage", 1, function() {
    App::assets()->addScript("util.js");
    App::assets()->addStyle("general.css");
    App::assets()->addStyle("actions.css");
});

App::hook(Hooks\PreRegistrarGetContactDetails::class);

App::hook(Hooks\AdminAreaPage::class, null, 10);
App::hook("AdminAreaHeadOutput", Hooks\CheckCredentials::class);
App::hook("AdminHomepage", Hooks\SyncExpiry::class, 2);
App::hook("AdminHomepage", Hooks\ImportDomains::class, 2);
App::hook("AdminHomepage", Hooks\AutoRenewStatus::class ,2);


App::hook(Hooks\AdminAreaHeadOutput::class, null, 100);
App::hook(Hooks\AdminAreaFooterOutput::class, null, 100);
App::hook(Hooks\ClientAreaPage::class);
App::hook(Hooks\ContactEdit::class);


App::hook('AdminHomeWidgets', Hooks\Widgets\ToolsWidget::class);
//App::hook('AdminHomeWidgets', Hooks\Widgets\DomainOverviewWidget::class);

App::hook('ClientAreaHeadOutput', Hooks\HeadAssets::class, 100);
App::hook('ClientAreaFooterOutput', Hooks\FooterAssets::class, 100);
App::hook('AdminAreaHeadOutput', Hooks\HeadAssets::class, 100);
App::hook('AdminAreaFooterOutput', Hooks\FooterAssets::class, 100);

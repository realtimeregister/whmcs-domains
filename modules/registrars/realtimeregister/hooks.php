<?php

use RealtimeRegister\App;
use RealtimeRegister\Hooks;

require_once __DIR__ . '/vendor/autoload.php';

App::boot();

App::hook(Hooks\PreRegistrarGetContactDetails::class);

App::hook(Hooks\AdminAreaPage::class, 10);
App::hook("AdminAreaHeadOutput", Hooks\CheckCredentials::class, 1);

App::hook(Hooks\AdminAreaHeadOutput::class, null, 100);
App::hook(Hooks\AdminAreaFooterOutput::class, null, 100);
App::hook(Hooks\ClientAreaPage::class);
App::hook(Hooks\ContactEdit::class);
App::hook("AdminAreaHeadOutput", Hooks\ImportDomains::class);

App::hook('AdminHomeWidgets', Hooks\Widgets\BalanceWidget::class);
App::hook('AdminHomeWidgets', Hooks\Widgets\DomainOverviewWidget::class);

App::hook('ClientAreaHeadOutput', Hooks\HeadAssets::class, 100);
App::hook('ClientAreaFooterOutput', Hooks\FooterAssets::class, 100);
App::hook('AdminAreaHeadOutput', Hooks\HeadAssets::class, 100);
App::hook('AdminAreaFooterOutput', Hooks\FooterAssets::class, 100);

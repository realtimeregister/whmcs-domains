<?php

use RealtimeRegister\App;
use RealtimeRegister\Hooks;

require_once __DIR__ . '/vendor/autoload.php';

App::boot();

App::hook(Hooks\PreRegistrarGetContactDetails::class);
App::hook("AdminAreaHeadOutput", Hooks\CheckCredentials::class, 1);

App::hook(Hooks\AdminAreaHeadOutput::class, null, 100);
App::hook(Hooks\AdminAreaFooterOutput::class,null, 100);
App::hook(Hooks\ClientAreaPage::class);
App::hook(Hooks\ContactEdit::class);




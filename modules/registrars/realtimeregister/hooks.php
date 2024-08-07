<?php

use RealtimeRegister\App;
use RealtimeRegister\Hooks;

require_once __DIR__ . '/vendor/autoload.php';

App::boot();

App::hook(Hooks\AdminAreaHeadOutput::class, 100);
App::hook(Hooks\AdminAreaFooterOutput::class, 100);
App::hook(Hooks\ClientAreaPage::class);
App::hook(Hooks\ContactEdit::class);

App::hook(Hooks\PreRegistrarGetContactDetails::class);

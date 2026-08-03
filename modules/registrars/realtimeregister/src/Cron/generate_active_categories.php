<?php

/**
 * This script generates a list of tlds where the auto registration provider is set to Realtime Register. The output can
 * be used to add a categories.json to resources/domains, which will show you in the tld import a new tab called
 * 'Registered' so you know which tlds are currently active for Realtime Register.
 *
 * Example usage:
 *
 * ```
 * php src/Cron/generate_active_categories.php > ../../../resources/domains/categories.json
 * ```
 *
 */

namespace RealtimeRegisterDomains\Cron;

use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Models\Whmcs\DomainPricing;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', '..', '..', 'init.php']);
if (defined('ROOTDIR')) {
    require_once ROOTDIR . '/includes/registrarfunctions.php';
}

$domainPricingList = DomainPricing::where('autoreg', 'realtimeregister')->get()->all();

$key = 'Used at Realtime Register';

$results[$key] = [];
foreach ($domainPricingList as $domainPricing) {
    $results[$key][] = substr(App::toPunycode('zut' . $domainPricing->extension), 3);
}

if (count($results[$key]) > 0) {
    echo json_encode($results, JSON_PRETTY_PRINT);
}

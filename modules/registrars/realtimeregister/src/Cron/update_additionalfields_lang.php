<?php

namespace Realtimeregister\Cron;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', '..', '..', 'init.php']);
require_once ROOTDIR . '/includes/registrarfunctions.php';

use RealtimeRegister\Services\MetadataService;

ini_set('max_execution_time', 0);

$tlds = MetadataService::getAllTlds();
$regex = '/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/';
$entry = "\$_LANG['%s'] = '%s';\n";
$lines = [];

foreach ($tlds as $tld) {
    try {
        $metadata = new MetadataService($tld);
        $properties = $metadata->get('contactProperties');
        if ($properties) {
            foreach ($properties as $property) {
                $label = $property['label'];
                if (strlen($label) > 70) {
                    $name = str_replace(['-', '_'], ' ', $property['name']);
                    $label = ucfirst(strtolower(preg_replace($regex, ' \0', $name)));
                }
                $label = addcslashes($label, "'");
                $description = addcslashes($property['description'], "'");
                $langvar = MetadataService::toLangVar($tld, $property['name']);
                $lines[] = sprintf($entry, $langvar . '_label', $label);
                $lines[] = sprintf($entry, $langvar . '_description', $description);

                if ($property['values']) {
                    foreach ($property['values'] as $key => $value) {
                        $lines[] = sprintf($entry, $langvar . '_' . $key, $value);
                    }
                }
            }
            $lines[] = "\n";
        }
    } catch (\Exception $e) {
        logActivity("Exception in update_additional_fields while getting metadata for " . $tld . ": " . $e->getMessage());
        continue;
    }
}

if ($lines) {
    $disclaimer = <<<DQL
/**
 * WHMCS Language File for RTR module additional domain fields
 * English (en)
 *
 * Please Note: This language file is automatically generated and therefore
 * editing of this file directly is not advised. Instead we recommend that
 * you use overrides to customise the text displayed in a way which will
 * be persistent.
 *
 * For instructions on overrides, please visit:
 * http://docs.whmcs.com/Language_Overrides
 */


DQL;

    array_unshift($lines, "<?php\n", $disclaimer);
    file_put_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', 'lang', 'english.additional.php']), $lines);
}

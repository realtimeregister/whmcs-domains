<?php

/*
  --------------------------------------------------------------------
  ///  WHMCS DOMAIN ADDITIONAL FIELDS  \\\
  --------------------------------------------------------------------

  This is where you can define the TLD specific fields required to
  register certain TLDs. Supported variables are:

  Name - key name used to reference field in modules (required)
  DisplayName - name displayed in client & admin interfaces
  LangVar - the language file variable to use if set
  Type - field type: text, dropdown, radio, tickbox (required)
  Size - the length of the text field
  Default - the default value the field should take
  Required - force entry - true/false

  --------------------------------------------------------------------
 */

use RealtimeRegisterDomains\Services\LogService;
use RealtimeRegisterDomains\Services\MetadataService;

// Use this variable to skip getting the metadata for any tld's, add custom fields at the bottom of the file
$skip = ['nl'];
$tlds = [];
$languageCodes = [];

if (isset($_SESSION['cart']['domains'])) {
    foreach ($_SESSION['cart']['domains'] as $domain) {
        $tld = MetadataService::getTld($domain['domain']);
        if ($domain['idnLanguage']) {
            $languageCodes[$tld] = $domain['idnLanguage'];
        }
        $tlds[] = $tld;
    }
}

if (!empty($_POST['domain']) && is_string($_POST['domain'])) {
    $tlds[] = MetadataService::getTld($_POST['domain']);
}

// global $domain is set in the admin area when viewing a domain
global $domain;

$dom = is_object($domain) ? $domain->domain : $domain;
if (!empty($dom) && is_string($dom)) {
    $tlds[] = MetadataService::getTld($dom);
}

$tlds = array_unique($tlds);

foreach ($tlds as $tld) {
    if (in_array($tld, $skip)) {
        continue;
    }
    try {
        $additional = (new MetadataService($tld))->getTldAdditionalFields($languageCodes[$tld]);
    } catch (\Exception $e) {
        LogService::logError($e, "Error while getting additional fields for '" . $tld . "' for Realtime Register:");
        $additional = [];
    }

    if (empty($additional)) {
        continue;
    }

    $fields = $additional['fields'] ?? [];

    if ($tld !== 'aero') {
        $fields = array_filter(
            $fields,
            function ($field) {
                return !in_array($field['Name'], ['AeroId', 'AeroKey']);
            }
        );
    }

    $additionaldomainfields['.' . $tld] = $fields;

    // Add 'applicable' TLD's, mainly for SLD support
    foreach ($additional['applicableFor'] as $item) {
        $additionaldomainfields['.' . $item] = $fields;
    }
}

if (!empty($additionaldomainfields['.eu'])) {
    if (!empty($_SESSION['uid'])) {
        $client = localAPI('GetClientsDetails', ['clientid' => $_SESSION['uid'], 'stats' => false]);
        if (!empty($client['companyname'])) {
            foreach ($additionaldomainfields['.eu'] as $i => $field) {
                if ($field['Name'] === 'countryOfCitizenship') {
                    unset($additionaldomainfields['.eu'][$i]);
                }
            }
        }
    }
}

if (!empty($additionaldomainfields['.coop'])) {
    foreach ($additionaldomainfields['.coop'] as $i => $field) {
        if ($field['Name'] === 'coopAcceptRequirements') {
            $additionaldomainfields['.coop'][$i]['Required'] = true;
        }
    }
}

/* Add custom fields here, example
   $additionaldomainfields[".nl"][] = [
    "Name" => "Name1",
    "LangVar" => "langvar_1",
    "Type" => "dropdown",
    "Options" => "option1|Option 1,option2|Option2,
    "Default" => "option1",
    ];
    ...
*/

MetadataService::removeDefaultFields($additionaldomainfields);

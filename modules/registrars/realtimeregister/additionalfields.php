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

use RealtimeRegister\Services\MetadataService;

$tlds = [];

if (isset($_SESSION['cart']['domains'])) {
    $tlds = array_map(function ($domain) { return MetadataService::getTld($domain['domain']); }, $_SESSION['cart']['domains']);
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
    try {
        $additional = (new MetadataService($tld))->getTldAdditionalFields();
    } catch (\Exception $e) {
        logActivity("Error while getting additional fields for '" . $tld . "' for Realtime Register:" . $e->getMessage());
        $additional = [];
    }

    if (empty($additional)) {
        continue;
    }

    $fields = $additional['fields'] ?? [];

    if ($tld !== 'aero') {
        $fields = array_filter($fields, function ($field) {
            return !in_array($field['Name'], ['AeroId', 'AeroKey']);
        });
    }

    if (empty($fields)) {
        continue;
    }

    $additionaldomainfields['.' . $tld] = $fields;
    // Add 'applicable' TLD's, mainly for SLD support
    foreach ($additional['applicableFor'] as $item) {
        $additionaldomainfields['.' . $item] = $fields;
    }
}

// Unset NL metadata
if (!empty($additionaldomainfields['.nl'])) {
    unset($additionaldomainfields['.nl']);
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

MetadataService::removeDefaultFields($additionaldomainfields);
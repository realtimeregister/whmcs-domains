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

$tlds = array();

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
        $additional = (new MetadataService($tld, null))->getTldAdditionalFields();
    } catch (\Exception $e) {
        //TODO
    }

    if (empty($additional)) {
        continue;
    }

    $fields = $additional['fields'];

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

// .nu
if (!empty($additionaldomainfields['.nu'])) {
    foreach ($additionaldomainfields['.nu'] as &$field) {
        if ($field['Name'] === 'orgno') {
            $field['Description'] = 'Corporate identity number or personal identification number for Swedish contacts (for non Swedish, any other unique identification number can be used instead) Use the format as detailed above with the country-code that applies for the registrant country.';
        }
    }
}

// .se
if (!empty($additionaldomainfields['.se'])) {
    foreach ($additionaldomainfields['.se'] as &$field) {
        if ($field['Name'] === 'orgno') {
            $field['Description'] = 'Corporate identity number or personal identification number for Swedish contacts (for non Swedish, any other unique identification number can be used instead) Use the format as detailed above with the country-code that applies for the registrant country.';
        }
    }
}

// .it
if (!empty($additionaldomainfields['.it'])) {
    foreach ($additionaldomainfields['.it'] as &$field) {
        if ($field['Name'] === 'entityType') {
            $field['Description'] = 'Registrant legal form; 1 for personal registrations Italian & EU based, 2-6 for Italian based companies & 7 for non-Italian EU based companies.';
        }
        if ($field['Name'] === 'regCode') {
            $field['Description'] = 'Registrant identification number; VAT/Company number for companies or Unique personal identication number for personal registrations.';
        }
        if ($field['Name'] === 'consentForPublishing') {
            $field['Description'] = 'Allow the publication of registrant personal data in the registry WHOIS. In case of a registration based on an organisation name (options 2-7 in the entity type field), this option always has to be turned on.';
        }
    }
}

// .es
if (!empty($additionaldomainfields['.es'])) {
    foreach ($additionaldomainfields['.es'] as &$field) {
        if ($field['Name'] === 'identificationNumber') {
            $field['Description'] = 'Unique data set for personal registrations outside Spain, Organisation number for companies outside of Spain, the NIF number for Spanish registrants or the NIE number for foreign companies residing in Spain.';
        }
        if ($field['Name'] === 'entityType') {
            $field['Description'] = 'Choose option 1 for natural persons or one of the other options for companies/organisations.';
        }
    }
}

// .sg
if (!empty($additionaldomainfields['.sg'])) {
    foreach ($additionaldomainfields['.sg'] as &$field) {
        if ($field['Name'] === 'COMPANY-NUMBER') {
            $field['Description'] = 'Only submit the company number is the registrant is a company';
        }
        if ($field['Name'] === 'IDCARD-OR-PASSPORT-NUMBER') {
            $field['Description'] = 'Only submit the ID card number of the registrant in case the registrant is a natural person';
        }
        if ($field['Name'] === 'SG-ADMIN-SINGPASSID') {
            $field['Description'] = 'Only submit the SP access ID in case the registrant resides in Singapore';
        }
        if ($field['Name'] === 'SG-ADMIN-RCBID') {
            $field['Description'] = 'Only submit the Admin contact SP access ID in case the admin contact resides in Singapore';
        }
    }
}

// .dk
if (!empty($additionaldomainfields['.dk'])) {
    foreach ($additionaldomainfields['.dk'] as &$field) {
        if ($field['Name'] === 'orgtype') {
            $field['Description'] = 'Provide the entity type with one of the three options, only mandatory for Danish companies.';
        }
        if ($field['Name'] === 'eanno') {
            $field['Description'] = 'Provide the EAN number of the company, only mandatory for Danish public organisations';
        }
        if ($field['Name'] === 'vatno') {
            $field['Description'] = 'Provide the VAT-number of the company you’re registering for, mandatory for all companies both Danish and Foreign.';
        }
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

// Remove remaining default WHMCS additional fields, for WHMCS 7+
rtrHelper::removeWhmcsDefaultAdditionalFields($additionaldomainfields);

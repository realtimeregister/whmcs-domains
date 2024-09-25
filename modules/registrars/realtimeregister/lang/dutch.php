<?php

/**
 * WHMCS Language File for RTR module
 * Dutch (nl)
 *
 * Please Note: These language files are overwritten during software updates
 * and therefore editing of these files directly is not advised. Instead we
 * recommend that you use overrides to customise the text displayed in a way
 * which will be safely preserved through the upgrade process.
 *
 * For instructions on overrides, please visit:
 * http://docs.whmcs.com/Language_Overrides
 */

$_LANG['rtr']['managechildhosts'] = "Beheer Child Hosts";
$_LANG['rtr']['childhostmanagement'] = "Child Hosts beheer";
$_LANG['rtr']['dnssecmanagement'] = "DNS Sec beheer";
$_LANG['rtr']['addnew'] = "Voeg nieuwe toe";
$_LANG['rtr']['uniquenameserver'] = "Unieke naamserver.";
$_LANG['rtr']['ipaddress'] = "IP adres.";
$_LANG['rtr']['version'] = "Versie.";
$_LANG['rtr']['manageips'] = "Beheer IPs";
$_LANG['rtr']['deletehost'] = "Verwijder";
$_LANG['rtr']['back'] = "Terug";
$_LANG['rtr']['save'] = "Opslaan";
$_LANG['rtr']['deleteipaddressinstruction'] = "Verwijder IP adressen hieronder.";
$_LANG['rtr']['additionalchildhosts'] = "Extra Child Hosts";
$_LANG['rtr']['add_keydata'] = "+ Voeg keydata toe";
$_LANG['rtr']['something_went_wrong'] = 'Er is iets mis gegaan:';
$_LANG['rtr']['add_ip'] = '+ voeg ip toe';

$_LANG['rtr']['adac']['suggestions'] = 'Misschien zijn deze domeinnamen ook interessant:';
$_LANG['rtr']['adac']['premium_not_supported'] = 'Premium domeinen worden niet ondersteund';
$_LANG['rtr']['adac']['premium'] = 'Premium';
$_LANG['rtr']['adac']['transfer'] = 'Verhuizen';
$_LANG['rtr']['adac']['register'] = 'Registreren';
$_LANG['rtr']['adac']['checkout'] = 'Afrekenen';

$_LANG['rtr']['adac']['status']['0'] = "Controleren...";
$_LANG['rtr']['adac']['status']['1'] = "Beschikbaar";
$_LANG['rtr']['adac']['status']['2'] = "Bezet";
$_LANG['rtr']['adac']['status']['3'] = "Niet geldig";
$_LANG['rtr']['adac']['status']['4'] = "Geen reactie";
$_LANG['rtr']['adac']['status']['5'] = "Onbekend";

$_LANG['rtr_languagecode_label'] = "Selecteer taal code";
$_LANG['rtr_languagecode_description'] = "";

$_LANG['rtr']['custom_handles']['please_wait'] = 'Een moment geduld alstublieft, we laden de gegevens nu in..';
$_LANG['rtr']['custom_handles']['custom_properties'] = 'Aangepaste eigenschappen';
$_LANG['rtr']['custom_handles']['close'] = 'Sluiten';
$_LANG['rtr']['custom_handles']['save'] = 'Opslaan';
$_LANG['rtr']['custom_handles']['error'] = 'Er ging iets mis, controlleer uw invoer en probeer opnieuw';

$_LANG['rtr']['update_available'] = 'Er is een update beschikbaar van de Realtime Register plugin.';
$_LANG['rtr']['update_notification']['prerelease'] = 'Prerelease';
$_LANG['rtr']['update_notification']['view_update'] = 'Bekijk update';
$_LANG['rtr']['update_notification']['newest'] = 'Nieuwste versie';

$_LANG['rtr']['flags'] = 'Vlaggen';
$_LANG['rtr']['protocol'] = 'Protocol';
$_LANG['rtr']['algorithm'] = 'Algorithme';
$_LANG['rtr']['public_key'] = 'Publieke sleutel';

$_LANG['rtr']['date'] = 'Datum';
$_LANG['rtr']['action'] = 'Actie';
$_LANG['rtr']['status'] = 'Status';

$_LANG['rtr']['transfer_log']['type'] = 'Type';
$_LANG['rtr']['transfer_log']['message'] = 'Bericht';

$_LANG['rtr']['actions']['import_domains'] = 'Importeer domeinen';
$_LANG['rtr']['actions']['import_into_whmcs'] = 'Importeer domeinen/clienten vanuit RealtimeRegister naar WHMCS';
$_LANG['rtr']['actions']['sync_expire_dates'] = 'Sync Verloopdatums';
$_LANG['rtr']['actions']['sync_all_expire_dates'] = 'Sync de Renew date voor alle domeinen in uw WHMCS account';
$_LANG['rtr']['actions']['change_autorenew_status'] = 'Verander Auto Renew Status';
$_LANG['rtr']['actions']['change_autorenew_to_false'] = 'Verander van alle domeinen de autorenew status naar false bij RealtimeRegister';

include_once implode(
    DIRECTORY_SEPARATOR,
    [ROOTDIR, 'modules', 'registrars', 'realtimeregister', 'lang', 'dutch.additional.php']
);

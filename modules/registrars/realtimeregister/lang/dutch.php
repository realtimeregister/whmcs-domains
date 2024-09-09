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

include_once implode(DIRECTORY_SEPARATOR, [ROOTDIR, 'modules', 'registrars', 'realtimeregister', 'lang', 'dutch.additional.php']);

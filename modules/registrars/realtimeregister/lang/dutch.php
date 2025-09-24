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

$_LANG['rtr']['update_notification']['update_available']
    = 'Er is een update beschikbaar van de Realtime Register plugin.';
$_LANG['rtr']['update_notification']['prerelease'] = 'Prerelease';
$_LANG['rtr']['update_notification']['view_update'] = 'Bekijk update';
$_LANG['rtr']['update_notification']['newest'] = 'Nieuwste versie';

$_LANG['rtr']['flags'] = 'Vlaggen';
$_LANG['rtr']['protocol'] = 'Protocol';
$_LANG['rtr']['algorithm'] = 'Algorithme';
$_LANG['rtr']['public_key'] = 'Publieke sleutel';

$_LANG['rtr']['date'] = 'Datum';
$_LANG['rtr']['type'] = 'Soort';
$_LANG['rtr']['action'] = 'Actie';
$_LANG['rtr']['status'] = 'Status';

$_LANG['rtr']['transfer_log']['type'] = 'Type';
$_LANG['rtr']['transfer_log']['message'] = 'Bericht';

$_LANG['rtr']['promotions']['explanation'] = 'Dit zijn de huidige en toekomstige promoties';
$_LANG['rtr']['promotions']['product'] = 'Product';
$_LANG['rtr']['promotions']['price'] = 'Prijs';
$_LANG['rtr']['promotions']['from_date'] = 'Begindatum';
$_LANG['rtr']['promotions']['end_date'] = 'Einddatum';
$_LANG['rtr']['promotions']['none'] = 'We hebben geen promoties gevonden';

$_LANG['rtr']['inactive_domains']['explanation'] = 'Deze domainen moeten gecontroleerd worden, ze kunnen nu niet ' .
    'gebruikt worden totdat de problemen aan de kant van de registry opgelost worden';
$_LANG['rtr']['inactive_domains']['none'] = 'Goezo! Er zijn geen domeinen met een \'invalid\' status aanwezig!';
$_LANG['rtr']['inactive_domains']['since'] = 'Sinds: ';

$_LANG['rtr']['actions']['import_domains'] = 'Importeer domeinen';
$_LANG['rtr']['actions']['import_into_whmcs'] = 'Importeer domeinen/clienten vanuit RealtimeRegister naar WHMCS';
$_LANG['rtr']['actions']['sync_expire_dates'] = 'Sync Verloopdatums';
$_LANG['rtr']['actions']['sync_all_expire_dates'] = 'Sync de Renew date voor alle domeinen in uw WHMCS account';
$_LANG['rtr']['actions']['change_autorenew_status'] = 'Verander Auto Renew Status';
$_LANG['rtr']['actions']['change_autorenew_to_false']
    = 'Verander van alle domeinen de autorenew status naar false bij RealtimeRegister';

$_LANG['rtr']['dns']['name'] = 'Naam';
$_LANG['rtr']['dns']['soa_records'] = 'SOA records';
$_LANG['rtr']['dns']['nothing_selected'] = '-Niets geselecteerd-';
$_LANG['rtr']['dns']['type'] = 'Type';
$_LANG['rtr']['dns']['content'] = 'Inhoud';
$_LANG['rtr']['dns']['ttl'] = 'TTL';
$_LANG['rtr']['dns']['prio'] = 'prio';
$_LANG['rtr']['dns']['not_in_control'] = 'We hebben niet de controle over de domeinservers van ":domain", je zult moeten gaan naar de eigenaar van de dnsservers, welke zijn te vinden op';
$_LANG['rtr']['dns']['no_records_yet'] = 'Je hebt nog geen dns records, begin vandaag nog!';
$_LANG['rtr']['dns']['add_new_row'] = 'Voeg nieuwe rij toe';
$_LANG['rtr']['dns']['save'] = 'Opslaan';
$_LANG['rtr']['dns']['dns_settings'] = 'DNS instellingen';
$_ADDONLANG['rtr']['dns']['dns_settings_pagetitle'] = 'Verander DNS instellingen van ":domain"';
$_LANG['rtr']['dns']['save_successful'] = 'De DNS wijzigingen zijn doorgevoerd';
$_LANG['rtr']['dns']['save_error'] = 'Er is iets mis gegaan bij het opslaan van de DNS records';
$_LANG['rtr']['dns']['not_found_heading'] = 'Domein niet gevonden';
$_LANG['rtr']['dns']['not_found_text'] = 'Het domein wat je geselecteerd hebt is onbekend, of je hebt er geen toegang toe';
$_LANG['rtr']['dns']['hostmaster'] = 'Hostmaster';
$_LANG['rtr']['dns']['refresh'] = 'Refresh';
$_LANG['rtr']['dns']['retry'] = 'Retry';
$_LANG['rtr']['dns']['expire'] = 'Expire';

include_once implode(
    DIRECTORY_SEPARATOR,
    [ROOTDIR, 'modules', 'registrars', 'realtimeregister', 'lang', 'dutch.additional.php']
);

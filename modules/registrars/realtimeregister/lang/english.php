<?php

/**
 * WHMCS Language File for RTR module
 * English (en)
 *
 * Please Note: These language files are overwritten during software updates
 * and therefore editing of these files directly is not advised. Instead we
 * recommend that you use overrides to customise the text displayed in a way
 * which will be safely preserved through the upgrade process.
 *
 * For instructions on overrides, please visit:
 * http://docs.whmcs.com/Language_Overrides
 */

$_LANG['rtr']['managechildhosts'] = "Manage Child Hosts";
$_LANG['rtr']['childhostmanagement'] = "Child Hosts Management";
$_LANG['rtr']['dnssecmanagement'] = "DNSSec Management";
$_LANG['rtr']['addnew'] = "Add New";
$_LANG['rtr']['uniquenameserver'] = "Your unique name server.";
$_LANG['rtr']['ipaddress'] = "IP address.";
$_LANG['rtr']['version'] = "Version.";
$_LANG['rtr']['manageips'] = "Manage IPs";
$_LANG['rtr']['deletehost'] = "Delete";
$_LANG['rtr']['back'] = "Back";
$_LANG['rtr']['save'] = "Save";
$_LANG['rtr']['deleteipaddressinstruction'] = "In order to delete IP address, simply remove it.";
$_LANG['rtr']['additionalchildhosts'] = "Additional Child Hosts";
$_LANG['rtr']['add_keydata'] = "+ Add keydata";
$_LANG['rtr']['something_went_wrong'] = 'Something went wrong:';
$_LANG['rtr']['saved'] = 'Changes saved succesfully';
$_LANG['rtr']['add_ip'] = '+ add ip';

$_LANG['rtr']['adac']['suggestions'] = 'Need suggestions? You might also like:';
$_LANG['rtr']['adac']['premium_not_supported'] = 'Premium domains are not supported';
$_LANG['rtr']['adac']['premium'] = 'Premium';
$_LANG['rtr']['adac']['transfer'] = 'Transfer';
$_LANG['rtr']['adac']['register'] = 'Register';
$_LANG['rtr']['adac']['checkout'] = 'Checkout';

$_LANG['rtr']['adac']['status']['0'] = "Checking...";
$_LANG['rtr']['adac']['status']['1'] = "available";
$_LANG['rtr']['adac']['status']['2'] = "taken";
$_LANG['rtr']['adac']['status']['3'] = "invalid";
$_LANG['rtr']['adac']['status']['4'] = "No response";
$_LANG['rtr']['adac']['status']['5'] = "unknown";


// Additional domain fields
$_LANG['rtr_languagecode_label'] = 'Language Code';
$_LANG['rtr_languagecode_description'] = '';

$_LANG['rtr']['custom_handles']['please_wait'] = 'Please wait while we load the content..';
$_LANG['rtr']['custom_handles']['custom_properties'] = 'Custom contact handles';
$_LANG['rtr']['custom_handles']['info'] = 'Custom handle overrides for specific registries, the handles must exist ' .
    'at Realtime Register. To create a contact, go <a href="//dm.realtimeregister.com/app/contacts/create" ' .
    'class="underlined">here</a>.';
$_LANG['rtr']['custom_handles']['close'] = 'Close';
$_LANG['rtr']['custom_handles']['save'] = 'Save';
$_LANG['rtr']['custom_handles']['error'] = 'Something went wrong, please try again after reviewing your data';
$_LANG['rtr']['custom_handles']['handles'] = 'The following handles do not exist: ';
$_LANG['rtr']['update_notification']['update_available']
    = 'There is an update available for the Realtime Register plugin.';
$_LANG['rtr']['update_notification']['prerelease'] = 'Prerelease';
$_LANG['rtr']['update_notification']['view_update'] = 'View update';
$_LANG['rtr']['update_notification']['newest'] = 'Newest version';

$_LANG['rtr']['flags'] = 'Flags';
$_LANG['rtr']['protocol'] = 'Protocol';
$_LANG['rtr']['algorithm'] = 'Algorithm';
$_LANG['rtr']['public_key'] = 'Public key';

$_LANG['rtr']['date'] = 'Date';
$_LANG['rtr']['action'] = 'Action';
$_LANG['rtr']['status'] = 'Status';

$_LANG['rtr']['transfer_log']['type'] = 'Type';
$_LANG['rtr']['transfer_log']['message'] = 'Message';

$_LANG['rtr']['promotions']['explanation'] = 'These are the current and/or upcoming promotions';
$_LANG['rtr']['promotions']['product'] = 'Product';
$_LANG['rtr']['promotions']['price'] = 'Price';
$_LANG['rtr']['promotions']['from_date'] = 'Start date';
$_LANG['rtr']['promotions']['end_date'] = 'End date';
$_LANG['rtr']['promotions']['none'] = 'We didn\'t find any promotions';

$_LANG['rtr']['actions']['import_domains'] = 'Import domains';
$_LANG['rtr']['actions']['import_into_whmcs'] = 'Import domains/clients from RealtimeRegister into WHMCS';
$_LANG['rtr']['actions']['sync_expire_dates'] = 'Sync Expiry Dates';
$_LANG['rtr']['actions']['sync_all_expire_dates'] = 'Sync the expiry date for all domains in your WHMCS account';
$_LANG['rtr']['actions']['change_autorenew_status'] = 'Change Auto Renew Status';
$_LANG['rtr']['actions']['change_autorenew_to_false']
    = 'Change the domains autorenew status to false at RealtimeRegister';

$_LANG['rtr']['errorlog'] = [
    'details' => 'Error Log Details',
    'filename' => 'Filename',
    'classname' => 'Class Name',
    'linenumber' => 'Line Number',
    'message' => 'Message',
    'time' => 'Time',
    'stacktrace' => 'Stack Trace'
];

$_LANG['rtr']['process']['info'] = 'For more information on any process in the listing, click the corresponding row to 
navigate to the  process in the Realtime Register Domain Manager.';

include_once implode(
    DIRECTORY_SEPARATOR,
    [ROOTDIR, 'modules', 'registrars', 'realtimeregister', 'lang', 'english.additional.php']
);

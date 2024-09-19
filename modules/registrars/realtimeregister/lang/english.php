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
$_LANG['rtr_languagecode_label'] = 'Select Language Code';
$_LANG['rtr_languagecode_description'] = '';

include_once implode(
    DIRECTORY_SEPARATOR,
    [ROOTDIR, 'modules', 'registrars', 'realtimeregister', 'lang', 'english.additional.php']
);

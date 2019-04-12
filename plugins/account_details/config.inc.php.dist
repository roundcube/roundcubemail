<?php

/*
	Account Details options
	default config last updated in version 2009-09-26
	
	To hide a row, do not remove the variable, but set it: 'false'

*/

$account_details_config = array();

// Bullet Style - Default &#9679;
$account_details_config['bulletstyle'] = '&#9775;'; // Insert your favorite unicode here. https://www.w3schools.com/charsets/ref_utf_misc_symbols.asp

// URL Text box length - Good if you have a long domain name
$account_details_config['urlboxlength'] = '90'; // Numbers only

// === ACCOUNT/WEBMAIL/SERVER INFO ===================
// Display Roundcube Info
$account_details_config['display_rc'] = true;
$account_details_config['display_rc_version'] = true;
$account_details_config['display_rc_release'] = true;

/* Hourly Cron Job is required to be setup as follows: 
 * curl https://api.github.com/repos/roundcube/roundcubemail/releases | grep tag_name | grep -o "[0-9].[0-9].[0-9]\{1,\}" | sort -n | tail -1 >> /path_to_roundcube/plugins/account_details/rc_latest.txt
*/
$account_details_config['rc_latest'] = 'plugins/account_details/rc_latest.txt';

// Display Plugin List
$account_details_config['rc_pluginlist'] = true;

// Enable display of used/total quota
$account_details_config['enable_userid'] = true;
$account_details_config['enable_quota'] = true;

// Display last webmail login / create
$account_details_config['display_create'] = true;
$account_details_config['display_lastlogin'] = true;

// Enable User IP Address
$account_details_config['enable_ip'] = true; // Simple IP Address from your system IP Only - Shows LAN IP if behind firewall
$account_details_config['useipinfo'] = true; // Displays IP, City and Region provided by http://ipinfo.io/ - Free up to 1000 hits a day - No Registration required

// Display Support URL - If enabled, Be sure and fill this out in Roundcubes main config.inc.php if not specified during installation
$account_details_config['enable_support'] = true;

// Server location (Example: 'City, Country')
$account_details_config['location'] = 'The Great State of Texas'; // Cannot be blank. Messes with tables

//========= Mailbox Details ==========================
// Display Mailbox Details - Unread Count - Total Count - Size of Folder
$account_details_config['enable_mailbox'] = true;
// Enable or Disable Individual Folders - 'enable_mailbox' Must be true
$account_details_config['enable_drafts'] = true; 
$account_details_config['enable_sent'] = true; 
$account_details_config['enable_junk'] = true; 
$account_details_config['enable_trash'] = true; 
$account_details_config['enable_archive'] = true; 
//====================================================

// Display User System Details
$account_details_config['enable_osystem'] = true;

// Display Browser Information
$account_details_config['enable_browser'] = true;

// Display Resolution Settings
$account_details_config['enable_resolution'] = true;

//======== Hosting Server Details ====================
// Displays Information for YOUR Server ... USE AT YOUR OWN RISK TRUST IN USERS REQUIRED

// Display your Server OS
$account_details_config['enable_server_os'] = true; // Only one of the 3 options below should be true otherwise multiple tables will show OS.
	//==== Enable only one out of 3 below ===============
// Display only Server name
$account_details_config['enable_server_os_name'] = false;
// Display Server name and Release
$account_details_config['enable_server_os_rel'] = false;
// Display Server name Release and version
$account_details_config['enable_server_os_ver'] = true;
	//===================================================

// Display Server CPU Load
$account_details_config['enable_server_cpu'] = true;

// Display Server Memory
$account_details_config['enable_server_memory'] = true;

// Display Server Uptime
$account_details_config['enable_server_uptime'] = true;
// Display PHP Server 
$account_details_config['display_php_version'] = true;
// Display HTTP Server 
$account_details_config['display_http_server'] = true;
// Display Server Admin Email 
$account_details_config['display_admin_email'] = true;
//====================================================

// For the next three URL/host variables, you can use these
// characters for dynamic replacement:
//     %p is replaced with the current protocol (http or https)
//     %h is replaced with the current mail server name
//     %H is replaced with the current mail server name;
//        everything to and including the fist dot stripped
//     %s is replaced with current http server name
//     %S is replaced with current http server name;
//        everything to and including the fist dot stripped

// Webmail URL
$account_details_config['webmail_url'] = '%p://%s/mail/';

// Server hostname
$account_details_config['hostname'] = '%S';
// If you have different server host names per protocol
$account_details_config['hostname_smtp'] = 'mx.%H';
$account_details_config['hostname_imap'] = 'mx.%H';
$account_details_config['hostname_pop'] = 'mx.%H';

// === SERVER CAPABILITIES ==========================================

// Port numbers in arrays (multiple port numbers allowed).
// Assign empty string '' instead of array to hide the row.
$account_details_config['port_smtp'] = array('25');
$account_details_config['port_imap'] = array('143');
$account_details_config['port_pop'] = array('110');
$account_details_config['port_smtp-ssl'] = array('465');
$account_details_config['port_imap-ssl'] = array('993');
$account_details_config['port_pop-ssl'] = array('995');

// SPA auth support for protocols
$account_details_config['spa_support_smtp'] = true;  // SPA auth for SMTP
$account_details_config['spa_support_imap'] = true;  // SPA auth for IMAP
$account_details_config['spa_support_pop'] = true;   // SPA auth for POP

// Authentication always required on SMTP:
$account_details_config['smtp_auth_required_always'] = true;

// If previous setting is set to false, the following info will be used instead:
$account_details_config['smtp_relay_local'] = true;         // Open relay for hosts on server network
$account_details_config['smtp_after_pop'] = false;          // SMTP after POP support
$account_details_config['smtp_after_imap'] = false;         // SMTP after POP support
$account_details_config['smtp_auth_required_else'] = true;  // SMTP auth required if not


// Add a (recommended) note to the SSL port numbers header?
$account_details_config['recommendssl'] = true; // TODO Doesn't work as expexted

// Add a newline between the port numbers and the port notes
// if false - put in pharentesises instead
$account_details_config['pn_newline'] = false;

// Enable CalDAV and CardDAV URL's
$account_details_config['enable_dav_urls'] = true;

// === CUSTOM INFORMATION: ADD TO SERVER INFO BOX ======================

// You can add customized fields at the end of each category, or add fields to 
// the bottom of Server Information box. These main variable name defines
// where to add the content:
//     $account_details_config['customfields_account'][]
//     $account_details_config['customfields_webmail'][]
//     $account_details_config['customfields_server'][]
//     $account_details_config['customfields_regularports'][]
//     $account_details_config['customfields_encryptedports'][]
//     $account_details_config['customfields_bottom'][]
//
// Add as many fields as you like, with the content in 'text' and one of the
// following row types in set in 'type':
//     ['header'] will create a category header
//     ['wholeline'] will span over both columns
//     ['title'] will create a title tag (left)
//     ['value'] will create a value tag (right)
// A 'title' must be proceeded by a 'value' to not mess up the table layout.
// See the commented lines below for examples. HTML tags will work.
// Even PHP code will do, but keep in mind that you cannot print directly,
// you need to assign a value.
//
// You can also use language handling. As an example, change:
//    'sky'
// into:
//    $this->gettext('sky')
// and add this line to the language files (at least en_US.inc) in the localization/ folder:
//    $labels['sky'] = 'Sky';

// Example of a custom line in the webmail category:
//$account_details_config['customfields_webmail'][] = array('type' => 'title', 'text' => $this->gettext('webmailsystem'));
//$account_details_config['customfields_webmail'][] = array('type' => 'value', 'text' => 'Roundcube ' . RCMAIL_VERSION);
// Example of some custom content at bottom of the account_details box:
//$account_details_config['customfields_bottom'][] = array('type' => 'header', 'text' => 'Color information');
//$account_details_config['customfields_bottom'][] = array('type' => 'wholeline', 'text' => '<i>Here are some handy infos about colors:</i>');
//$account_details_config['customfields_bottom'][] = array('type' => 'title', 'text' => 'Sky');
//$account_details_config['customfields_bottom'][] = array('type' => 'value', 'text' => 'Blue');
//$account_details_config['customfields_bottom'][] = array('type' => 'title', 'text' => 'Grass');
//$account_details_config['customfields_bottom'][] = array('type' => 'value', 'text' => 'Green');
//$account_details_config['customfields_bottom'][] = array('type' => 'title', 'text' => 'Sun');
//$account_details_config['customfields_bottom'][] = array('type' => 'value', 'text' => 'Yellow');
//$account_details_config['customfields_bottom'][] = array('type' => 'wholeline',
//     'text' => $this->_print_file_contents('plugins/account_details/custom_includes/example_field_1.html'));



// === CUSTOM INFORMATION: SEPARATE BOX AT BOTTOM OF PAGE ===================

// Include a file with customized text in it's own box at the bottom
// This implementation is limited - PHP not possible, no language handling
$account_details_config['enable_custombox'] = false; //TODO
$account_details_config['custombox_header'] = 'Help a Brotha Out!';
$account_details_config['custombox_file'] = 'plugins/account_details/custom_includes/example_box_1.html';

$account_details_config['intro'] = 'plugins/account_details/custom_includes/intro.html';
$account_details_config['paypal'] = 'plugins/account_details/custom_includes/paypal.html';

?>

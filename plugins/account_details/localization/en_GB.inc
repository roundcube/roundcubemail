<?php

/*
 +-----------------------------------------------------------------------+
 | plugins/userinfo/localization/<lang>.inc                              |
 |                                                                       |
 | Localization file of the Roundcube Webmail Userinfo plugin            |
 | Copyright (C) 2012-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-userinfo/
*/

$labels = array();
$labels['account'] = 'User Details';
$labels['account_details'] = 'Account Details';
$labels['account_details_mo'] = 'Account Details Plugin';
$labels['accountinformation'] = 'Account Details';
$labels['and'] = 'and';
$labels['auth'] = 'Authentication';
$labels['addressbooks'] = 'Address Books';
$labels['archive'] = 'Archive Folder';
$labels['authrequired'] = 'Authentication Required';
$labels['back'] = 'Back';
$labels['browser'] = 'Web Browser: <br/>\n'; // : <br/>\n NEEDS TO BE INCLUDED
$labels['browser-version'] = 'Version:<br/>\n '; // : <br/>\n NEEDS TO BE INCLUDED
$labels['browser-user-agent'] = 'User Agent:<br/>\n'; // : <br/>\n NEEDS TO BE INCLUDED
$labels['calendars'] = 'Calendars';
$labels['clear_trash'] = 'Empty Trash';
$labels['clients'] = 'Recommended clients which support %s protocol';
$labels['copiedtoclipboard'] = 'Copied to clipboard';
$labels['copytoclipboard'] = 'Copy to clipboard';
$labels['created'] = 'Created';
$labels['description'] = 'Get detailed information about your account.';
$labels['default'] = 'Default';
$labels['defaultidentity'] = 'Name';
$labels['drafts'] = 'Drafts Folder';
$labels['emailaddress'] = 'Email Address';
$labels['enable_support'] = 'Support URL';
$labels['encryption'] = 'Encryption';
$labels['hostname'] = 'Hostname';
$labels['imap'] = 'Incoming IMAP';
$labels['imap-ssl'] = 'Incoming IMAP-SSL';
$labels['inbox'] = 'Inbox';
$labels['ipaddress'] = 'Current IP Address';
$labels['iplocation'] = 'IP Address from';
$labels['junk'] = 'Junk Folder';
$labels['lastlogin'] = 'Last Login';
$labels['location'] = 'Server Location';
$labels['mailbox'] = 'Mailbox Details';
$labels['no'] = 'No';
$labels['os'] = 'Operating System';
$labels['php_version'] = 'PHP Version';
$labels['pluginname'] = 'Account Details';
$labels['plugindescription'] = 'Displays basic account information such as IMAP, POP & SMTP server protocols, default Identity, user’s CalDAV & CardDAV URLs and other details.';
$labels['pop'] = 'Incoming POP';
$labels['pop-ssl'] = 'Incoming POP-SSL';
$labels['port'] = 'Port';
$labels['portnumbers'] = 'Port numbers';
$labels['portnumbersencrypted'] = 'encrypted connection to mail server';
$labels['portnumbersregular'] = 'regular connection to mail server';
$labels['rc_version'] = 'Roundcube Version';
$labels['recommended'] = 'SSL Recommended';
$labels['resolution'] = 'Screen Resolution';
$labels['sent'] = 'Sent Folder';
$labels['serverinfo'] = 'Server Details';
$labels['serverinformation'] = 'Server Information';
$labels['server'] = 'Server';
$labels['servercpu'] = 'Server CPU Load';
$labels['servermemory'] = 'Server Memory';
$labels['serveros'] = 'Server OS';
$labels['serveruptime'] = 'Server Uptime';
$labels['size'] = 'KB';
$labels['smtp'] = 'Outgoing SMTP';
$labels['smtp-ssl'] = 'Outgoing SMTP-SSL';
$labels['spaauthsupported'] = '&nbsp; SPA password encryption supported';
$labels['storagequota'] = 'Total Storage';
$labels['trash'] = 'Trash Folder';
$labels['total'] = 'Total';
$labels['tutorial'] = 'Tutorial &nbsp;&nbsp;';
$labels['unread'] = 'Unread';
$labels['usystem'] = 'User System Details';
$labels['url'] = 'URL';
$labels['usedstorage'] = 'Used Storage';
$labels['userinfo'] = 'User Info';
$labels['userid'] = 'System User ID';
$labels['used'] = 'Used';
$labels['username'] = 'Username';
$labels['webmail'] = 'Webmail';
$labels['webmailsystem'] = 'Webmail system';
$labels['web_url'] = 'Webmail URL';
$labels['yes'] = 'Yes';


// %s will be excanged with the appropiate "SMTP after *" below
$labels['openrelaylocal'] = 'Permitted to send when on same network as server';
$labels['authrequired_local'] = 'Authentication required, except when on the same network as server';
$labels['authrequired_local_smtpafter'] = 'Authentication or %s required, except when on the same network as server';
$labels['authrequired_smtpafter'] = 'Authentication or %s required';

// "SMTP after *" are to some extent known technical terminologies.
// For some languages it will probably be correct to not translate "after"
$labels['smtpafterpop'] = 'SMTP after POP';
$labels['smtpafterimap'] = 'SMTP after IMAP';
$labels['smtpafterpopimap'] = 'SMTP after POP/IMAP';

?>
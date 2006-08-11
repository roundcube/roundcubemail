<?php

/*
 +-----------------------------------------------------------------------+
 | language/en_US/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Tobias 'tri' Richter <tobias@datenwerkstatt-richter.de>       |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Welcome to $product';
$labels['username']  = 'Username';
$labels['password']  = 'Password';
$labels['server']    = 'Server';
$labels['login']     = 'Login';

// taskbar
$labels['logout']   = 'Logout';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Personal Settings';
$labels['addressbook'] = 'Address Book';

// mailbox names
$labels['inbox']  = 'Inbox';
$labels['drafts'] = 'Drafts';
$labels['sent']   = 'Sent';
$labels['trash']  = 'Trash';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Subject';
$labels['from']    = 'Sender';
$labels['to']      = 'Recipient';
$labels['cc']      = 'Copy';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Reply-To';
$labels['date']    = 'Date';
$labels['size']    = 'Size';
$labels['priority'] = 'Priority';
$labels['organization'] = 'Organization';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Folders';
$labels['messagesfromto'] = 'Messages $from to $to of $count';
$labels['messagenrof'] = 'Message $nr of $count';

$labels['moveto']   = 'move to...';
$labels['download'] = 'download';

$labels['filename'] = 'File name';
$labels['filesize'] = 'File size';

$labels['preferhtml'] = 'Prefer HTML';
$labels['htmlmessage'] = 'HTML Message';
$labels['prettydate'] = 'Pretty dates';

$labels['addtoaddressbook'] = 'Add to address book';

// weekdays short
$labels['sun'] = 'Sun';
$labels['mon'] = 'Mon';
$labels['tue'] = 'Tue';
$labels['wed'] = 'Wed';
$labels['thu'] = 'Thu';
$labels['fri'] = 'Fri';
$labels['sat'] = 'Sat';

// weekdays long
$labels['sunday']    = 'Sunday';
$labels['monday']    = 'Monday';
$labels['tuesday']   = 'Tuesday';
$labels['wednesday'] = 'Wednesday';
$labels['thursday']  = 'Thursday';
$labels['friday']    = 'Friday';
$labels['saturday']  = 'Saturday';

$labels['today'] = 'Today';

// toolbar buttons
$labels['checkmail']        = 'Check for new messages';
$labels['writenewmessage']  = 'Create a new message';
$labels['replytomessage']   = 'Reply to the message';
$labels['replytoallmessage'] = 'Reply to sender and all recipients';
$labels['forwardmessage']   = 'Forward the message';
$labels['deletemessage']    = 'Move message to trash';
$labels['printmessage']     = 'Print this message';
$labels['previousmessages'] = 'Show previous set of messages';
$labels['nextmessages']     = 'Show next set of messages';
$labels['backtolist']       = 'Back to message list';
$labels['viewsource']       = 'Show source';

$labels['select'] = 'Select';
$labels['all'] = 'All';
$labels['none'] = 'None';
$labels['unread'] = 'Unread';

$labels['compact'] = 'Compact';
$labels['empty'] = 'Empty';
$labels['purge'] = 'Purge';

$labels['quota'] = 'Disk usage';
$labels['unknown']  = 'unknown';
$labels['unlimited']  = 'unlimited';

$labels['quicksearch']  = 'Quick search';
$labels['resetsearch']  = 'Reset search';


// message compose
$labels['compose']  = 'Compose a message';
$labels['savemessage']  = 'Save this draft';
$labels['sendmessage']  = 'Send the message now';
$labels['addattachment']  = 'Attach a file';
$labels['charset']  = 'Charset';
$labels['returnreceipt'] = 'Return receipt';

$labels['checkspelling'] = 'Check spelling';
$labels['resumeediting'] = 'Resume editing';
$labels['revertto'] = 'Revert to';

$labels['attachments'] = 'Attachments';
$labels['upload'] = 'Upload';
$labels['close']  = 'Close';

$labels['low']     = 'Low';
$labels['lowest']  = 'Lowest';
$labels['normal']  = 'Normal';
$labels['high']    = 'High';
$labels['highest'] = 'Highest';

$labels['nosubject']  = '(no subject)';
$labels['showimages'] = 'Display images';


// address boook
$labels['name']			= 'Display name';
$labels['first_name']		= 'First name';
$labels['last_name']		= 'Last name';
$labels['email']		= 'E-Mail';
$labels['middle_name']		= 'Middle Name';
$labels['edu_title']		= 'Education Title';
$labels['addon']		= 'Addon';
$labels['nickname']		= 'Nickname';
$labels['company']		= 'Company';
$labels['organisation']		= 'Organisation';
$labels['department']		= 'Department';
$labels['job_title']		= 'Job Title';
$labels['note']			= 'Note';
$labels['tel_work1_voice']	= 'Tel Work1 Voice';
$labels['tel_work2_voice']	= 'Tel Work2 Voice';
$labels['tel_home1_voice']	= 'Tel Home1 Voice';
$labels['tel_home2_voice']	= 'Tel Home2 Voice';
$labels['tel_cell_voice']	= 'Tel Cell Voice';
$labels['tel_car_voice']	= 'Tel Car Voice';
$labels['tel_pager_voice']	= 'Tel Pager Voice';
$labels['tel_additional']	= 'Tel Additional';
$labels['tel_work_fax']		= 'Tel Work Fax';
$labels['tel_home_fax']		= 'Tel Home Fax';
$labels['tel_isdn']		= 'Tel ISDN';
$labels['tel_preferred']	= 'Tel Preferred';
$labels['tel_telex']		= 'Tel Telex';
$labels['work_street']		= 'Work Street';
$labels['work_zip']		= 'Work Zip';
$labels['work_city']		= 'Work City';
$labels['work_region']		= 'Work Region';
$labels['work_country']		= 'Work Country';
$labels['home_street']		= 'Home Street';
$labels['home_zip']		= 'Home Zip';
$labels['home_city']		= 'Home City';
$labels['home_region']		= 'Home Region';
$labels['home_country']		= 'Home Country';
$labels['postal_street']	= 'Postal Street';
$labels['postal_zip']		= 'Postal Zip';
$labels['postal_city']		= 'Postal City';
$labels['postal_region']	= 'Postal Region';
$labels['postal_country']	= 'Postal Country';
$labels['url_work']		= 'URL Work';
$labels['role']			= 'Role';
$labels['birthday']		= 'Birthday';
$labels['rev']			= 'Revision';
$labels['lang']			= 'Language';

$labels['addcontact'] = 'Add new contact';
$labels['editcontact'] = 'Edit contact';

$labels['edit']   = 'Edit';
$labels['cancel'] = 'Cancel';
$labels['save']   = 'Save';
$labels['delete'] = 'Delete';

$labels['newcontact']     = 'Create new contact card';
$labels['deletecontact']  = 'Delete selected contacts';
$labels['composeto']      = 'Compose mail to';
$labels['contactsfromto'] = 'Contacts $from to $to of $count';
$labels['print']          = 'Print';
$labels['export']         = 'Export';

$labels['previouspage']   = 'Show previous set';
$labels['nextpage']       = 'Show next set';


// LDAP search
$labels['ldapsearch'] = 'LDAP directory search';

$labels['ldappublicsearchname']    = 'Contact name';
$labels['ldappublicsearchtype'] = 'Exact match?';
$labels['ldappublicserverselect'] = 'Select servers';
$labels['ldappublicsearchfield'] = 'Search on';
$labels['ldappublicsearchform'] = 'Look for a contact';
$labels['ldappublicsearch'] = 'Search';


// settings
$labels['settingsfor']  = 'Settings for';

$labels['preferences']  = 'Preferences';
$labels['userpreferences']  = 'User preferences';
$labels['editpreferences']  = 'Edit user preferences';

$labels['identities']  = 'Identities';
$labels['manageidentities']  = 'Manage identities for this account';
$labels['newidentity']  = 'New identity';

$labels['newitem']  = 'New item';
$labels['edititem']  = 'Edit item';

$labels['setdefault']  = 'Set default';
$labels['language']  = 'Language';
$labels['timezone']  = 'Time zone';
$labels['pagesize']  = 'Rows per page';
$labels['signature'] = 'Signature';
$labels['dstactive']  = 'Daylight savings';

$labels['folder']  = 'Folder';
$labels['folders']  = 'Folders';
$labels['foldername']  = 'Folder name';
$labels['subscribed']  = 'Subscribed';
$labels['create']  = 'Create';
$labels['createfolder']  = 'Create new folder';
$labels['rename'] = 'Rename';
$labels['renamefolder'] = 'Rename folder';
$labels['deletefolder']  = 'Delete folder';
$labels['managefolders']  = 'Manage folders';

$labels['sortby'] = 'Sort by';
$labels['sortasc']  = 'Sort ascending';
$labels['sortdesc'] = 'Sort descending';

?>

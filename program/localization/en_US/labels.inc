<?php

/*

 +-----------------------------------------------------------------------+
 | language/en_US/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 @version $Id$

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

$labels['preferhtml'] = 'Display HTML';
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

// months short
$labels['jan']	= 'Jan';
$labels['feb']	= 'Feb';
$labels['mar']	= 'Mar';
$labels['apr']	= 'Apr';
$labels['may']	= 'May';
$labels['jun']	= 'Jun';
$labels['jul'] 	= 'Jul';
$labels['aug']	= 'Aug';
$labels['sep']	= 'Sep';
$labels['oct']	= 'Oct';
$labels['nov']	= 'Nov';
$labels['dec']	= 'Dec';

// months long
$labels['longjan']	= 'January';
$labels['longfeb']	= 'February';
$labels['longmar']	= 'March';
$labels['longapr']	= 'April';
$labels['longmay']	= 'May';
$labels['longjun']	= 'June';
$labels['longjul']	= 'July';
$labels['longaug']	= 'August';
$labels['longsep']	= 'September';
$labels['longoct']	= 'October';
$labels['longnov']	= 'November';
$labels['longdec']	= 'December';

$labels['today'] = 'Today';

// toolbar buttons
$labels['checkmail']        = 'Check for new messages';
$labels['writenewmessage']  = 'Create a new message';
$labels['replytomessage']   = 'Reply to sender';
$labels['replytoallmessage'] = 'Reply to sender and all recipients';
$labels['forwardmessage']   = 'Forward the message';
$labels['deletemessage']    = 'Delete message';
$labels['movemessagetotrash'] = 'Move message to trash';
$labels['printmessage']     = 'Print this message';
$labels['previousmessage']  = 'Show the previous message';
$labels['previousmessages'] = 'Show previous set of messages';
$labels['firstmessage']     = 'Show the first message';
$labels['firstmessages']    = 'Show first set of messages';
$labels['nextmessage']      = 'Show the next message';
$labels['nextmessages']     = 'Show next set of messages';
$labels['lastmessage']      = 'Show the last message';
$labels['lastmessages']     = 'Show last set of messages';
$labels['backtolist']       = 'Back to message list';
$labels['viewsource']       = 'Show source';
$labels['markmessages']     = 'Mark messages';
$labels['markread']         = 'As read';
$labels['markunread']       = 'As unread';

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
$labels['compose']        = 'Compose a message';
$labels['savemessage']    = 'Save this draft';
$labels['sendmessage']    = 'Send the message now';
$labels['addattachment']  = 'Attach a file';
$labels['charset']        = 'Charset';
$labels['editortype']     = 'Editor type';
$labels['returnreceipt']  = 'Return receipt';

$labels['checkspelling'] = 'Check spelling';
$labels['resumeediting'] = 'Resume editing';
$labels['revertto']      = 'Revert to';

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

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Plain text';

$labels['addcc'] = 'Add Cc';
$labels['addbcc'] = 'Add Bcc';
$labels['addreplyto'] = 'Add Reply-To';

// mdn
$labels['mdnrequest'] = 'The sender of this message has asked to be notified when you read this message. Do you wish to notify the sender?';
$labels['receiptread'] = 'Return Receipt (read)';
$labels['yourmessage'] = 'This is a Return Receipt for your message';
$labels['receiptnote'] = 'Note: This receipt only acknowledges that the message was displayed on the recipient\'s computer. There is no guarantee that the recipient has read or understood the message contents.';

// address boook
$labels['name']      = 'Display name';
$labels['firstname'] = 'First name';
$labels['surname']   = 'Last name';
$labels['email']     = 'E-Mail';

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
$labels['firstpage']      = 'Show first set';
$labels['nextpage']       = 'Show next set';
$labels['lastpage']       = 'Show last set';

$labels['groups'] = 'Groups';
$labels['personaladrbook'] = 'Personal Addresses';


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
$labels['dstactive']  = 'Daylight saving time';
$labels['htmleditor'] = 'Compose HTML messages';
$labels['htmlsignature'] = 'HTML signature';
$labels['previewpane'] = 'Show preview pane';
$labels['logoutclear'] = 'Clear Trash on logout';
$labels['logoutcompact'] = 'Compact Inbox on logout';
$labels['uisettings'] = 'User Interface';
$labels['serversettings'] = 'Server Settings';
$labels['mailboxview'] = 'Mailbox View';
$labels['mdnrequests'] = 'Sender notifications';
$labels['askuser'] = 'ask the user';
$labels['autosend'] = 'send automatically';
$labels['ignore'] = 'ignore';
$labels['readwhendeleted'] = 'Mark the message as read on delete';
$labels['flagfordeletion'] = 'Flag the message for deletion instead of delete';

$labels['autosavedraft']  = 'Automatically save draft';
$labels['everynminutes']  = 'every $n minutes';
$labels['never']  = 'never';

$labels['folder']  = 'Folder';
$labels['folders']  = 'Folders';
$labels['foldername']  = 'Folder name';
$labels['subscribed']  = 'Subscribed';
$labels['messagecount'] = 'Messages';
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

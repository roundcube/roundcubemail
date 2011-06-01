<?php

/*

 +-----------------------------------------------------------------------+
 | language/en_US/labels.inc                                             |
 |                                                                       |
 | Language file of the Roundcube Webmail client                         |
 | Copyright (C) 2005-2010, The Roundcube Dev Team                       |
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
$labels['settings'] = 'Settings';
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
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'Date';
$labels['size']    = 'Size';
$labels['priority'] = 'Priority';
$labels['organization'] = 'Organization';
$labels['readstatus'] = 'Read status';

$labels['mailboxlist'] = 'Folders';
$labels['messagesfromto'] = 'Messages $from to $to of $count';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'Message $nr of $count';

$labels['copy']     = 'Copy';
$labels['move']     = 'Move';
$labels['moveto']   = 'Move to...';
$labels['download'] = 'Download';

$labels['filename'] = 'File name';
$labels['filesize'] = 'File size';

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
$labels['replytoallmessage'] = 'Reply to list or to sender and all recipients';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'Forward the message';
$labels['deletemessage']    = 'Delete message';
$labels['movemessagetotrash'] = 'Move message to trash';
$labels['printmessage']     = 'Print this message';
$labels['previousmessage']  = 'Show previous message';
$labels['firstmessage']     = 'Show first message';
$labels['nextmessage']      = 'Show next message';
$labels['lastmessage']      = 'Show last message';
$labels['backtolist']       = 'Back to message list';
$labels['viewsource']       = 'Show source';
$labels['markmessages']     = 'Mark messages';
$labels['markread']         = 'As read';
$labels['markunread']       = 'As unread';
$labels['markflagged']      = 'As flagged';
$labels['markunflagged']    = 'As unflagged';
$labels['messageactions']   = 'More actions...';

$labels['select'] = 'Select';
$labels['all'] = 'All';
$labels['none'] = 'None';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'Unread';
$labels['flagged'] = 'Flagged';
$labels['unanswered'] = 'Unanswered';
$labels['deleted'] = 'Deleted';
$labels['invert'] = 'Invert';
$labels['filter'] = 'Filter';

$labels['list'] = 'List';
$labels['threads'] = 'Threads';
$labels['expand-all'] = 'Expand All';
$labels['expand-unread'] = 'Expand Unread';
$labels['collapse-all'] = 'Collapse All';
$labels['threaded'] = 'Threaded';

$labels['autoexpand_threads'] = 'Expand message threads';
$labels['do_expand'] = 'all threads';
$labels['expand_only_unread'] = 'only with unread messages';
$labels['fromto'] = 'Sender/Recipient';
$labels['flag'] = 'Flag';
$labels['attachment'] = 'Attachment';
$labels['nonesort'] = 'None';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'Compact';
$labels['empty'] = 'Empty';

$labels['quota'] = 'Disk usage';
$labels['unknown']  = 'unknown';
$labels['unlimited']  = 'unlimited';

$labels['quicksearch']  = 'Quick search';
$labels['resetsearch']  = 'Reset search';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Entire message';

$labels['openinextwin'] = 'Open in new window';
$labels['emlsave'] = 'Download (.eml)';

// message compose
$labels['compose']        = 'Compose a message';
$labels['editasnew']      = 'Edit as new';
$labels['savemessage']    = 'Save this draft';
$labels['sendmessage']    = 'Send now';
$labels['addattachment']  = 'Attach a file';
$labels['charset']        = 'Charset';
$labels['editortype']     = 'Editor type';
$labels['returnreceipt']  = 'Return receipt';
$labels['dsn']            = 'Delivery status notification';

$labels['editidents']    = 'Edit identities';
$labels['checkspelling'] = 'Check spelling';
$labels['resumeediting'] = 'Resume editing';
$labels['revertto']      = 'Revert to';

$labels['attachments'] = 'Attachments';
$labels['upload'] = 'Upload';
$labels['close']  = 'Close';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'Low';
$labels['lowest']  = 'Lowest';
$labels['normal']  = 'Normal';
$labels['high']    = 'High';
$labels['highest'] = 'Highest';

$labels['nosubject']  = '(no subject)';
$labels['showimages'] = 'Display images';
$labels['alwaysshow'] = 'Always show images from $sender';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Plain text';
$labels['savesentmessagein'] = 'Save sent message in';
$labels['dontsave'] = 'don\'t save';
$labels['maxuploadsize'] = 'Maximum allowed file size is $size';

$labels['addcc'] = 'Add Cc';
$labels['addbcc'] = 'Add Bcc';
$labels['addreplyto'] = 'Add Reply-To';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'The sender of this message has asked to be notified when you read this message. Do you wish to notify the sender?';
$labels['receiptread'] = 'Return Receipt (read)';
$labels['yourmessage'] = 'This is a Return Receipt for your message';
$labels['receiptnote'] = 'Note: This receipt only acknowledges that the message was displayed on the recipient\'s computer. There is no guarantee that the recipient has read or understood the message contents.';

// address boook
$labels['name']         = 'Display name';
$labels['firstname']    = 'First name';
$labels['surname']      = 'Last name';
$labels['middlename']   = 'Middle name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job title';
$labels['organization'] = 'Company';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden name';
$labels['email']        = 'E-Mail';
$labels['phone']        = 'Phone';
$labels['address']      = 'Address';
$labels['street']       = 'Street';
$labels['locality']     = 'City';
$labels['zipcode']      = 'Zip code';
$labels['region']       = 'Region';
$labels['country']      = 'Country';
$labels['birthday']     = 'Birthday';
$labels['anniversary']  = 'Anniversary';
$labels['website']      = 'Website';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Notes';
$labels['male']   = 'male';
$labels['female'] = 'female';
$labels['manager'] = 'Manager';
$labels['assistant'] = 'Assistant';
$labels['spouse'] = 'Spouse';
$labels['allfields'] = 'All fields';

$labels['typehome']   = 'Home';
$labels['typework']   = 'Work';
$labels['typeother']  = 'Other';
$labels['typemobile']  = 'Mobile';
$labels['typemain']  = 'Main';
$labels['typehomefax']  = 'Home Fax';
$labels['typeworkfax']  = 'Work Fax';
$labels['typecar']  = 'Car';
$labels['typepager']  = 'Pager';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Assistant';

$labels['addfield'] = 'Add field...';
$labels['addcontact'] = 'Add new contact';
$labels['editcontact'] = 'Edit contact';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'Edit';
$labels['cancel'] = 'Cancel';
$labels['save']   = 'Save';
$labels['delete'] = 'Delete';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';

$labels['newcontact']     = 'Create new contact card';
$labels['deletecontact']  = 'Delete selected contacts';
$labels['composeto']      = 'Compose mail to';
$labels['contactsfromto'] = 'Contacts $from to $to of $count';
$labels['print']          = 'Print';
$labels['export']         = 'Export';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['groupactions']   = 'Actions for contact groups...';

$labels['previouspage']   = 'Show previous page';
$labels['firstpage']      = 'Show first page';
$labels['nextpage']       = 'Show next page';
$labels['lastpage']       = 'Show last page';

$labels['group'] = 'Group';
$labels['groups'] = 'Groups';
$labels['personaladrbook'] = 'Personal Addresses';

$labels['import'] = 'Import';
$labels['importcontacts'] = 'Import contacts';
$labels['importfromfile'] = 'Import from file:';
$labels['importreplace'] = 'Replace the entire address book';
$labels['importtext'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> data format.';
$labels['done'] = 'Done';

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

$labels['preferhtml'] = 'Display HTML';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'HTML Message';
$labels['prettydate'] = 'Pretty dates';
$labels['setdefault']  = 'Set default';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'Language';
$labels['timezone']  = 'Time zone';
$labels['pagesize']  = 'Rows per page';
$labels['signature'] = 'Signature';
$labels['dstactive']  = 'Daylight saving time';
$labels['htmleditor'] = 'Compose HTML messages';
$labels['htmlonreply'] = 'on reply to HTML message only';
$labels['htmlsignature'] = 'HTML signature';
$labels['previewpane'] = 'Show preview pane';
$labels['skin'] = 'Interface skin';
$labels['logoutclear'] = 'Clear Trash on logout';
$labels['logoutcompact'] = 'Compact Inbox on logout';
$labels['uisettings'] = 'User Interface';
$labels['serversettings'] = 'Server Settings';
$labels['mailboxview'] = 'Mailbox View';
$labels['mdnrequests'] = 'On request for return receipt';
$labels['askuser'] = 'ask me';
$labels['autosend'] = 'send receipt';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'ignore';
$labels['readwhendeleted'] = 'Mark the message as read on delete';
$labels['flagfordeletion'] = 'Flag the message for deletion instead of delete';
$labels['skipdeleted'] = 'Do not show deleted messages';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'from known senders';
$labels['always'] = 'always';
$labels['showinlineimages'] = 'Display attached images below the message';
$labels['autosavedraft']  = 'Automatically save draft';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['keepalive']  = 'Check for new messages on';
$labels['never']  = 'never';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'Displaying Messages';
$labels['messagescomposition'] = 'Composing Messages';
$labels['mimeparamfolding'] = 'Attachment names';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (other)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'Advanced options';
$labels['focusonnewmessage'] = 'Focus browser window on new message';
$labels['checkallfolders'] = 'Check all folders for new messages';
$labels['displaynext'] = 'After message delete/move display the next message';
$labels['mainoptions'] = 'Main Options';
$labels['section'] = 'Section';
$labels['maintenance'] = 'Maintenance';
$labels['newmessage'] = 'New Message';
$labels['listoptions'] = 'List Options';
$labels['signatureoptions'] = 'Signature Options';
$labels['whenreplying'] = 'When replying';
$labels['replytopposting'] = 'start new message above original';
$labels['replybottomposting'] = 'start new message below original';
$labels['replyremovesignature'] = 'When replying remove original signature from message';
$labels['autoaddsignature'] = 'Automatically add signature';
$labels['newmessageonly'] = 'new message only';
$labels['replyandforwardonly'] = 'replies and forwards only';
$labels['replysignaturepos'] = 'When replying or forwarding place signature';
$labels['belowquote'] = 'below the quote';
$labels['abovequote'] = 'above the quote';
$labels['insertsignature'] = 'Insert signature';
$labels['previewpanemarkread']  = 'Mark previewed messages as read';
$labels['afternseconds']  = 'after $n seconds';
$labels['reqmdn'] = 'Always request a return receipt';
$labels['reqdsn'] = 'Always request a delivery status notification';
$labels['replysamefolder'] = 'Place replies in the folder of the message being replied to';
$labels['defaultaddressbook'] = 'Add new contacts to the selected addressbook';
$labels['spellcheckbeforesend'] = 'Check spelling before sending a message';

$labels['folder']  = 'Folder';
$labels['folders']  = 'Folders';
$labels['foldername']  = 'Folder name';
$labels['subscribed']  = 'Subscribed';
$labels['messagecount'] = 'Messages';
$labels['create']  = 'Create';
$labels['createfolder']  = 'Create new folder';
$labels['managefolders']  = 'Manage folders';
$labels['specialfolders'] = 'Special Folders';
$labels['properties'] = 'Properties';
$labels['folderproperties'] = 'Folder properties';
$labels['parentfolder'] = 'Parent folder';
$labels['location'] = 'Location';
$labels['info'] = 'Information';
$labels['getfoldersize'] = 'Click to get folder size';
$labels['changesubscription'] = 'Click to change subscription';
$labels['foldertype'] = 'Folder Type';
$labels['personalfolder']  = 'Private Folder';
$labels['otherfolder']  = 'Other User\'s Folder';
$labels['sharedfolder']  = 'Public Folder';

$labels['sortby'] = 'Sort by';
$labels['sortasc']  = 'Sort ascending';
$labels['sortdesc'] = 'Sort descending';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'English';
$labels['westerneuropean'] = 'Western European';
$labels['easterneuropean'] = 'Eastern European';
$labels['southeasterneuropean'] = 'South-Eastern European';
$labels['baltic'] = 'Baltic';
$labels['cyrillic'] = 'Cyrillic';
$labels['arabic'] = 'Arabic';
$labels['greek'] = 'Greek';
$labels['hebrew'] = 'Hebrew';
$labels['turkish'] = 'Turkish';
$labels['nordic'] = 'Nordic';
$labels['thai'] = 'Thai';
$labels['celtic'] = 'Celtic';
$labels['vietnamese'] = 'Vietnamese';
$labels['japanese'] = 'Japanese';
$labels['korean'] = 'Korean';
$labels['chinese'] = 'Chinese';

?>

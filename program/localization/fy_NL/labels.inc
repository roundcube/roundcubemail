<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
*/

$labels = array();

// login page
$labels['welcome']   = 'Welcome to $product';
$labels['username']  = 'Brûkersnamme';
$labels['password']  = 'Wachtwurd';
$labels['server']    = 'Server';
$labels['login']     = 'Ynlogge';

// taskbar
$labels['logout']   = 'Ôfmelde';
$labels['mail']     = 'Mail';
$labels['settings'] = 'Ynstellingen';
$labels['addressbook'] = 'Adresboek';

// mailbox names
$labels['inbox']  = 'Inbox';
$labels['drafts'] = 'Konsepten';
$labels['sent']   = 'Stjoerd';
$labels['trash']  = 'Jiskefet';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Ûnderwerp';
$labels['from']    = 'Fan';
$labels['sender']  = 'Sender';
$labels['to']      = 'Oan';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Antwurd oan';
$labels['followupto'] = 'Oanslutend oan';
$labels['date']    = 'Datum';
$labels['size']    = 'Size';
$labels['priority'] = 'Priority';
$labels['organization'] = 'Organisaasje';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'Mappen';
$labels['messagesfromto'] = 'Messages $from to $to of $count';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'Message $nr of $count';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'Kopieare';
$labels['move']     = 'Ferplaatse';
$labels['moveto']   = 'Move to...';
$labels['download'] = 'Delhelje';
$labels['showattachment'] = 'Toane';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'File name';
$labels['filesize'] = 'Triemgrutte';

$labels['addtoaddressbook'] = 'Add to address book';

// weekdays short
$labels['sun'] = 'sne';
$labels['mon'] = 'moa';
$labels['tue'] = 'tii';
$labels['wed'] = 'woa';
$labels['thu'] = 'toa';
$labels['fri'] = 'fre';
$labels['sat'] = 'sno';

// weekdays long
$labels['sunday']    = 'snein';
$labels['monday']    = 'moandei';
$labels['tuesday']   = 'tiisdei';
$labels['wednesday'] = 'woansdei';
$labels['thursday']  = 'tongersdei';
$labels['friday']    = 'freed';
$labels['saturday']  = 'sneon';

// months short
$labels['jan']	= 'jan';
$labels['feb']	= 'feb';
$labels['mar']	= 'mrt';
$labels['apr']	= 'apr';
$labels['may']	= 'maaie';
$labels['jun']	= 'jun';
$labels['jul'] 	= 'jul';
$labels['aug']	= 'aug';
$labels['sep']	= 'sep';
$labels['oct']	= 'okt';
$labels['nov']	= 'nov';
$labels['dec']	= 'des';

// months long
$labels['longjan']	= 'jannewaris';
$labels['longfeb']	= 'febrewaris';
$labels['longmar']	= 'maart';
$labels['longapr']	= 'april';
$labels['longmay']	= 'maaie';
$labels['longjun']	= 'juny';
$labels['longjul']	= 'july';
$labels['longaug']	= 'augustus';
$labels['longsep']	= 'septimber';
$labels['longoct']	= 'oktober';
$labels['longnov']	= 'novimber';
$labels['longdec']	= 'desimber';

$labels['today'] = 'Hjoed';

// toolbar buttons
$labels['refresh']          = 'Ferfarskje';
$labels['checkmail']        = 'Check for new messages';
$labels['compose']          = 'Compose';
$labels['writenewmessage']  = 'Create a new message';
$labels['reply']            = 'Beäntwurdzje';
$labels['replytomessage']   = 'Reply to sender';
$labels['replytoallmessage'] = 'Reply to list or to sender and all recipients';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'Forward';
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
$labels['mark']             = 'Mark';
$labels['markmessages']     = 'Mark messages';
$labels['markread']         = 'As read';
$labels['markunread']       = 'As unread';
$labels['markflagged']      = 'As flagged';
$labels['markunflagged']    = 'As unflagged';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'Select';
$labels['all'] = 'All';
$labels['none'] = 'None';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'Unread';
$labels['flagged'] = 'Flagged';
$labels['unanswered'] = 'Unanswered';
$labels['deleted'] = 'Deleted';
$labels['undeleted'] = 'Not deleted';
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
$labels['fromto'] = 'From/To';
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
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Open in new window';
$labels['emlsave'] = 'Download (.eml)';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'Send message';
$labels['savemessage']    = 'Save as draft';
$labels['addattachment']  = 'Attach a file';
$labels['charset']        = 'Charset';
$labels['editortype']     = 'Editor type';
$labels['returnreceipt']  = 'Return receipt';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'Check spelling';
$labels['resumeediting'] = 'Resume editing';
$labels['revertto']      = 'Revert to';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'Attachments';
$labels['upload'] = 'Upload';
$labels['uploadprogress'] = '$percent ($current from $total)';
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
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

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
$labels['name']         = 'Display Name';
$labels['firstname']    = 'First Name';
$labels['surname']      = 'Last Name';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'Email';
$labels['phone']        = 'Phone';
$labels['address']      = 'Address';
$labels['street']       = 'Street';
$labels['locality']     = 'City';
$labels['zipcode']      = 'ZIP Code';
$labels['region']       = 'State/Province';
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
$labels['search'] = 'Search';
$labels['advsearch'] = 'Advanced Search';
$labels['advanced'] = 'Advanced';
$labels['other'] = 'Other';

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
$labels['typehomepage']  = 'Home Page';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profile';

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
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'Create new contact card';
$labels['deletecontact']  = 'Delete selected contacts';
$labels['composeto']      = 'Compose mail to';
$labels['contactsfromto'] = 'Contacts $from to $to of $count';
$labels['print']          = 'Print';
$labels['export']         = 'Export';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Show previous page';
$labels['firstpage']      = 'Show first page';
$labels['nextpage']       = 'Show next page';
$labels['lastpage']       = 'Show last page';

$labels['group'] = 'Group';
$labels['groups'] = 'Groups';
$labels['personaladrbook'] = 'Personal Addresses';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = 'Import';
$labels['importcontacts'] = 'Import contacts';
$labels['importfromfile'] = 'Import from file:';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'Replace the entire address book';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'Done';

// settings
$labels['settingsfor'] = 'Settings for';
$labels['about'] = 'About';
$labels['preferences'] = 'Preferences';
$labels['userpreferences'] = 'User preferences';
$labels['editpreferences'] = 'Edit user preferences';

$labels['identities'] = 'Identities';
$labels['manageidentities'] = 'Manage identities for this account';
$labels['newidentity'] = 'New identity';

$labels['newitem'] = 'New item';
$labels['edititem'] = 'Edit item';

$labels['preferhtml'] = 'Display HTML';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'HTML Message';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'Pretty dates';
$labels['setdefault']  = 'Set default';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'Language';
$labels['timezone']  = 'Time zone';
$labels['pagesize']  = 'Rows per page';
$labels['signature'] = 'Signature';
$labels['dstactive']  = 'Daylight saving time';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Compose HTML messages';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
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
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'from known senders';
$labels['always'] = 'always';
$labels['showinlineimages'] = 'Display attached images below the message';
$labels['autosavedraft']  = 'Automatically save draft';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
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
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'Main Options';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'Section';
$labels['maintenance'] = 'Maintenance';
$labels['newmessage'] = 'New Message';
$labels['signatureoptions'] = 'Signature Options';
$labels['whenreplying'] = 'When replying';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'start new message above the quote';
$labels['replybottomposting'] = 'start new message below the quote';
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
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'Skip alternative email addresses in autocompletion';
$labels['listnamedisplay'] = 'List contacts as';
$labels['spellcheckbeforesend'] = 'Check spelling before sending a message';
$labels['spellcheckoptions'] = 'Spellcheck Options';
$labels['spellcheckignoresyms'] = 'Ignore words with symbols';
$labels['spellcheckignorenums'] = 'Ignore words with numbers';
$labels['spellcheckignorecaps'] = 'Ignore words with all letters capitalized';
$labels['addtodict'] = 'Add to dictionary';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'Folder';
$labels['folders']  = 'Mappen';
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
$labels['undo'] = 'Undo';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Source';
$labels['license'] = 'License';
$labels['support'] = 'Get support';

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

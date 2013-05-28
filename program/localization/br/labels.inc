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
$labels['welcome']   = 'Degemer mat war $product';
$labels['username']  = 'Anv an arveriad';
$labels['password']  = 'Ger-Tremen';
$labels['server']    = 'Dafariad';
$labels['login']     = 'Kennaskañ';

// taskbar
$labels['logout']   = 'Digennaskañ';
$labels['mail']     = 'Postel elektronek';
$labels['settings'] = 'Arventennoù';
$labels['addressbook'] = 'Karned chomlec\'hioù';

// mailbox names
$labels['inbox']  = 'Boest degemer';
$labels['drafts'] = 'Brouilhedoù';
$labels['sent']   = 'Kaset';
$labels['trash']  = 'Pod-lastez';
$labels['junk']   = 'Stroboù';

// message listing
$labels['subject'] = 'Sujed';
$labels['from']    = 'Eus';
$labels['sender']  = 'Sender';
$labels['to']      = 'Da';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Cci';
$labels['replyto'] = 'Respont-da';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'Deiz';
$labels['size']    = 'Ment';
$labels['priority'] = 'Kentwir';
$labels['organization'] = 'Aozadur';
$labels['readstatus'] = 'Stad al lenn';
$labels['listoptions'] = 'Dibarzhioù ar roll...';

$labels['mailboxlist'] = 'Teuliadoù';
$labels['messagesfromto'] = 'Kemennadoù eus $from da $to war $count';
$labels['threadsfromto'] = 'Sujedoù $from da $to war $count';
$labels['messagenrof'] = 'Kemennad $nr war $count';
$labels['fromtoshort'] = '$from - $to war $count';

$labels['copy']     = 'Eilañ';
$labels['move']     = 'Dilec\'hiañ';
$labels['moveto']   = 'Dilec\'hiañ da...';
$labels['download'] = 'Pellgargañ';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Diskouez';
$labels['showanyway'] = 'Diskouez anezhañ memestra';

$labels['filename'] = 'Anv ar restr';
$labels['filesize'] = 'Ment ar restr';

$labels['addtoaddressbook'] = 'Ouzhpennañ er c\'harned chomlec\'hioù';

// weekdays short
$labels['sun'] = 'Sul';
$labels['mon'] = 'Lun';
$labels['tue'] = 'Meu';
$labels['wed'] = 'Mer';
$labels['thu'] = 'Yao';
$labels['fri'] = 'Gwe';
$labels['sat'] = 'Sad';

// weekdays long
$labels['sunday']    = 'Sul';
$labels['monday']    = 'Lun';
$labels['tuesday']   = 'Meurzh';
$labels['wednesday'] = 'Merc\'her';
$labels['thursday']  = 'Yaou';
$labels['friday']    = 'Gwener';
$labels['saturday']  = 'Sadorn';

// months short
$labels['jan']	= 'Gen';
$labels['feb']	= 'C\'hwe';
$labels['mar']	= 'Meu';
$labels['apr']	= 'Ebr';
$labels['may']	= 'Mae';
$labels['jun']	= 'Mez';
$labels['jul'] 	= 'Gou';
$labels['aug']	= 'Eos';
$labels['sep']	= 'Gwe';
$labels['oct']	= 'Her';
$labels['nov']	= 'Du';
$labels['dec']	= 'Ker';

// months long
$labels['longjan']	= 'Genver';
$labels['longfeb']	= 'C\'hwevrer';
$labels['longmar']	= 'Meurzh';
$labels['longapr']	= 'Ebrel';
$labels['longmay']	= 'Mae';
$labels['longjun']	= 'Mezheven';
$labels['longjul']	= 'Gouere';
$labels['longaug']	= 'Eost';
$labels['longsep']	= 'Gwengolo';
$labels['longoct']	= 'Here';
$labels['longnov']	= 'Du';
$labels['longdec']	= 'Kerzu';

$labels['today'] = 'Hiziv';

// toolbar buttons
$labels['refresh']          = 'Azgrenaat';
$labels['checkmail']        = 'Gwiriañ kemennadoù nevez';
$labels['compose']          = 'Skrivañ ur c\'hemennad nevez';
$labels['writenewmessage']  = 'Skrivañ ur c\'hemennad nevez';
$labels['reply']            = 'Respont';
$labels['replytomessage']   = 'Respont d\'ar kaser';
$labels['replytoallmessage'] = 'Respont d\'an holl';
$labels['replyall']         = 'Respont d\'an holl';
$labels['replylist']        = 'Respont d\'ar roll';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'Treuzkas ar c\'hemennad';
$labels['deletemessage']    = 'Dilec\'hiañ ar c\'hemennad er pod-lastez';
$labels['movemessagetotrash'] = 'Move message to trash';
$labels['printmessage']     = 'Moulañ ar c\'hemennad-se';
$labels['previousmessage']  = 'Gwelout ar c"hemennad a-raok';
$labels['firstmessage']     = 'Gwelout ar c\'hemennad kentañ';
$labels['nextmessage']      = 'Gwelout ar c\'hemennad da heul';
$labels['lastmessage']      = 'Gwelout ar c\'hemennad diwezhañ';
$labels['backtolist']       = 'Distreiñ el listenn kemennadoù';
$labels['viewsource']       = 'Gwelout an tarzh';
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

$labels['select'] = 'Diuzañ';
$labels['all'] = 'An holl';
$labels['none'] = 'Ebet';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'Na lennet';
$labels['flagged'] = 'Flagged';
$labels['unanswered'] = 'Unanswered';
$labels['withattachment'] = 'With attachment';
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
$labels['nonesort'] = 'Ebet';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'Stummaat';
$labels['empty'] = 'Skarzhañ';

$labels['quota'] = 'Implijadur pladenn';
$labels['unknown']  = 'Dianav';
$labels['unlimited']  = 'didermen';

$labels['quicksearch']  = 'Klask prim';
$labels['resetsearch']  = 'Aderaouekaat ar glask';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Entire message';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Open in new window';
$labels['emlsave'] = 'Download (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'Kas ar c\'hemennad diouzhtu';
$labels['savemessage']    = 'Gwarediñ ar brouilhed-se';
$labels['addattachment']  = 'Stagañ ur restr';
$labels['charset']        = 'Engodadur';
$labels['editortype']     = 'Furmad aozer';
$labels['returnreceipt']  = 'Testeni degemer';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'Gwiriañ ar reizhskrivadur';
$labels['resumeediting'] = 'Distreiñ en aozer';
$labels['revertto']      = 'Distreiñ en/r';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'Restroù staget';
$labels['upload'] = 'Stagañ';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'Serriñ';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'Izel';
$labels['lowest']  = 'An izelañ';
$labels['normal']  = 'Reizh';
$labels['high']    = 'Uhel';
$labels['highest'] = 'An uhelañ';

$labels['nosubject']  = '(n\'eus ket sujed)';
$labels['showimages'] = 'Diskouez ar skeudennoù';
$labels['alwaysshow'] = 'Always show images from $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'testenn kriz';
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
$labels['name']         = 'Anv da ziskouez';
$labels['firstname']    = 'Raganv';
$labels['surname']      = 'Anv';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'Postel elektronek';
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
$labels['addcontact'] = 'Ouzhpennañ an darempred diuzet en hor c\'harned chomlec\'hioù';
$labels['editcontact'] = 'Cheñch an darempred';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'Cheñch';
$labels['cancel'] = 'Nullañ';
$labels['save']   = 'Gwarediñ';
$labels['delete'] = 'Dilemel';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'Krouiñ un darempred nevez';
$labels['deletecontact']  = 'Dilemel an darempredoù diuzet';
$labels['composeto']      = 'Skrivañ ur c\'hemennad ouzh';
$labels['contactsfromto'] = 'Darempredoù eus $from ouzh $to war $count';
$labels['print']          = 'Moulañ';
$labels['export']         = 'Ezporzhiañ';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Diskouez pajenn a-raok';
$labels['firstpage']      = 'Gwelout ar bajenn gentañ';
$labels['nextpage']       = 'Diskouez pajenn da heul';
$labels['lastpage']       = 'Gwelout ar bajenn diwezhañ';

$labels['group'] = 'Group';
$labels['groups'] = 'Strolladoù';
$labels['personaladrbook'] = 'Chomlec\'h personel';

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
$labels['settingsfor'] = 'Arventennoù evit';
$labels['about'] = 'About';
$labels['preferences'] = 'Dibarzhioù';
$labels['userpreferences'] = 'Dibarzhioù implijer';
$labels['editpreferences'] = 'Cheñch dibarzhioù implijer';

$labels['identities'] = 'Identelezhioù';
$labels['manageidentities'] = 'Merañ an identelezhioù evit ar c\'hont-se';
$labels['newidentity'] = 'Identelezh nevez';

$labels['newitem'] = 'Elfenn nevez';
$labels['edititem'] = 'Cheñch an elfenn';

$labels['preferhtml'] = 'Diskouez en HTML';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'Kemennad en HTML';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'Diskouez berr deizioù';
$labels['setdefault']  = 'Arventennoù dre ziouer';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'Yezh';
$labels['timezone']  = 'Gwerzhid-eur';
$labels['pagesize']  = 'Niver a linenn dre bajenn';
$labels['signature'] = 'Sinadur';
$labels['dstactive']  = 'Eur an hañv';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Sevel ur c\'hemennad er furmad HTML';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'Sinadur HTML';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'Diskouez panel alberz';
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
$labels['autosavedraft']  = 'Gwarediñ emgefre brouilhedoù';
$labels['everynminutes']  = 'Bep $n munutoù';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'ebet';
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

$labels['folder']  = 'Renkell';
$labels['folders']  = 'Teuliadoù';
$labels['foldername']  = 'Anv ar renkell';
$labels['subscribed']  = 'Koumananer';
$labels['messagecount'] = 'Messages';
$labels['create']  = 'Krouiñ';
$labels['createfolder']  = 'Krouiñ ur renkell nevez';
$labels['managefolders']  = 'Aozañ ar renkelloù';
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

$labels['sortby'] = 'Rummañ dre';
$labels['sortasc']  = 'Rumm diagentad';
$labels['sortdesc'] = 'Rumm diskennad';
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

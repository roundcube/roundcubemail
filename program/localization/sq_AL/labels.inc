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
$labels['welcome']   = 'Mirëseerdhe te $product';
$labels['username']  = 'Përdoruesi';
$labels['password']  = 'Fjalëkalimi';
$labels['server']    = 'Serveri';
$labels['login']     = 'Hyr';

// taskbar
$labels['logout']   = 'Dil';
$labels['mail']     = 'Mesazhet';
$labels['settings'] = 'Konfigurimi Personal';
$labels['addressbook'] = 'Libri i Adresave';

// mailbox names
$labels['inbox']  = 'Ardhjet';
$labels['drafts'] = 'Drafte';
$labels['sent']   = 'Dërguar';
$labels['trash']  = 'Fshirë';
$labels['junk']   = 'Reklama';

// message listing
$labels['subject'] = 'Tema';
$labels['from']    = 'Dërguesi';
$labels['sender']  = 'Sender';
$labels['to']      = 'Marrësi';
$labels['cc']      = 'Kopje';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Reply-To';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'Data';
$labels['size']    = 'Madhësia';
$labels['priority'] = 'Përparësia';
$labels['organization'] = 'Organizata';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'Kutitë';
$labels['messagesfromto'] = 'Mesazhet $from në $to nga $count';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'Mesazhi $nr nga $count';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'Kopje';
$labels['move']     = 'Move';
$labels['moveto']   = 'ço te...';
$labels['download'] = 'shkarko';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'Emër skedari';
$labels['filesize'] = 'Madhësi skedari';

$labels['addtoaddressbook'] = 'Shto në librin e adresave';

// weekdays short
$labels['sun'] = 'Dje';
$labels['mon'] = 'Hën';
$labels['tue'] = 'Mar';
$labels['wed'] = 'Mër';
$labels['thu'] = 'Enj';
$labels['fri'] = 'Pre';
$labels['sat'] = 'Sht';

// weekdays long
$labels['sunday']    = 'e djelë';
$labels['monday']    = 'e hënë';
$labels['tuesday']   = 'e martë';
$labels['wednesday'] = 'e mërkurë';
$labels['thursday']  = 'e enjte';
$labels['friday']    = 'e premte';
$labels['saturday']  = 'e shtunë';

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

$labels['today'] = 'sot';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'Shiko për mesazhe të reja';
$labels['compose']          = 'Shkruaj një mesazh';
$labels['writenewmessage']  = 'Krijo një mesazh të ri';
$labels['reply']            = 'Reply';
$labels['replytomessage']   = 'Ktheji përgjigje mesazhit';
$labels['replytoallmessage'] = 'Ktheji përgjigje dërguesit dhe të gjithë marrësve';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'Pasoje mesazhin';
$labels['deletemessage']    = 'Fshije mesazhin';
$labels['movemessagetotrash'] = 'Hidhe mesazhin në kosh';
$labels['printmessage']     = 'Shtype këtë mesazh';
$labels['previousmessage']  = 'Shfaq mesazhin e mëparshëm';
$labels['firstmessage']     = 'Shfaq mesazhin e parë';
$labels['nextmessage']      = 'Shfaq mesazhin pasardhës';
$labels['lastmessage']      = 'Shfaq mesazhin e fundit';
$labels['backtolist']       = 'Kthehu te lista e mesazheve';
$labels['viewsource']       = 'Shfaq tekstin';
$labels['mark']             = 'Mark';
$labels['markmessages']     = 'Shëno mesazhet';
$labels['markread']         = 'Si të lexuara';
$labels['markunread']       = 'Si të palexuara';
$labels['markflagged']      = 'As flagged';
$labels['markunflagged']    = 'As unflagged';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'Zgjidh';
$labels['all'] = 'Të gjitha';
$labels['none'] = 'Asnjë';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'Palexuar';
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
$labels['nonesort'] = 'Asnjë';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'Zvogëlo';
$labels['empty'] = 'Boshatis';

$labels['quota'] = 'Përdorimi i diskut';
$labels['unknown']  = 'i panjohur';
$labels['unlimited']  = 'i pakufizuar';

$labels['quicksearch']  = 'Kërkim i shpejtë';
$labels['resetsearch']  = 'Pastro kërkimin';
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
$labels['sendmessage']    = 'Dërgoje mesazhin tani';
$labels['savemessage']    = 'Ruaje si draft';
$labels['addattachment']  = 'Bashkangjit një skedar';
$labels['charset']        = 'Bashkësi shkronjash';
$labels['editortype']     = 'Lloj editori';
$labels['returnreceipt']  = 'Njoftim marrje';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'Kontroll gabimesh';
$labels['resumeediting'] = 'Vazhdo editimin';
$labels['revertto']      = 'Ktheje te';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'Bashkangjitjet';
$labels['upload'] = 'Ngarko';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'Mbyll';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'I ulët';
$labels['lowest']  = 'Më i ulëti';
$labels['normal']  = 'Normal';
$labels['high']    = 'I lartë';
$labels['highest'] = 'Më i larti';

$labels['nosubject']  = '(pa subjekt)';
$labels['showimages'] = 'Shfaq imazhet';
$labels['alwaysshow'] = 'Always show images from $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'TEXT';
$labels['savesentmessagein'] = 'Save sent message in';
$labels['dontsave'] = 'don\'t save';
$labels['maxuploadsize'] = 'Maximum allowed file size is $size';

$labels['addcc'] = 'Shto Cc';
$labels['addbcc'] = 'Shto Bcc';
$labels['addreplyto'] = 'Shto Reply-To';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'Dërguesi i këtij mesazhi ka kërkuar që të njoftohet kur ju të lexoni këtë mesazh. Dëshironi ta njoftoni dërguesin?';
$labels['receiptread'] = 'Njoftim Leximi';
$labels['yourmessage'] = 'Ky është një njoftim leximi për mesazhin tuaj';
$labels['receiptnote'] = 'Shënim: Ky njoftim tregon vetëm që mesazhi u shfaq në kompjuterin e marrësit. Nuk ka siguri që marrësi e ka lexuar ose e ka kuptuar përmbajtjen e mesazhit.';

// address boook
$labels['name']         = 'Emri që thirret';
$labels['firstname']    = 'Emri';
$labels['surname']      = 'Mbiemri';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'E-Mail';
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
$labels['addcontact'] = 'Shto një kontakt të ri';
$labels['editcontact'] = 'Ndrysho kontaktin';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'Ndrysho';
$labels['cancel'] = 'Anullo';
$labels['save']   = 'Ruaj';
$labels['delete'] = 'Fshi';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'Krijo nje kartë të re kontakti';
$labels['deletecontact']  = 'Fshi kontaktet e zgjedhura';
$labels['composeto']      = 'Shkruaj email për';
$labels['contactsfromto'] = 'Kontaktet $from në $to nga $count';
$labels['print']          = 'Shtyp';
$labels['export']         = 'Eksporto';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Shfaq grupin e mëparshëm';
$labels['firstpage']      = 'Shfaq grupin e parë';
$labels['nextpage']       = 'Shfaq grupin tjetër';
$labels['lastpage']       = 'Shfaq grupin e fundit';

$labels['group'] = 'Group';
$labels['groups'] = 'Grupet';
$labels['personaladrbook'] = 'Adresat Personale';

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
$labels['settingsfor'] = 'Konfigurimet për';
$labels['about'] = 'About';
$labels['preferences'] = 'Preferencat';
$labels['userpreferences'] = 'Preferencat e përdoruesit';
$labels['editpreferences'] = 'Ndrysho preferencat e përdoruesit';

$labels['identities'] = 'Identitetet';
$labels['manageidentities'] = 'Manaxho identitetet per këtë llogari';
$labels['newidentity'] = 'Identitet i ri';

$labels['newitem'] = 'Element i ri';
$labels['edititem'] = 'Ndrysho element';

$labels['preferhtml'] = 'Shfaq HTML';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'Mesazh HTML';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'Data të këndshme';
$labels['setdefault']  = 'Bëje identitet kryesor';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'Gjuha';
$labels['timezone']  = 'Brezi orar';
$labels['pagesize']  = 'Rreshtat për faqe';
$labels['signature'] = 'Nënshkrim';
$labels['dstactive']  = 'Ndryshim ore';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Shkruaj mesazhe HTML';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'Nënshkrimi HTML';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'Shiko si duket';
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
$labels['autosavedraft']  = 'Ruaje automatikisht si draft';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'kurrë';
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

$labels['folder']  = 'Kutia';
$labels['folders']  = 'Kutitë';
$labels['foldername']  = 'Emër kutie';
$labels['subscribed']  = 'Regjistruar';
$labels['messagecount'] = 'Mesazhe';
$labels['create']  = 'Krijo';
$labels['createfolder']  = 'Krijo kuti të re';
$labels['managefolders']  = 'Sistemo kutitë';
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

$labels['sortby'] = 'Rradhit sipas';
$labels['sortasc']  = 'Rradhitje rritëse';
$labels['sortdesc'] = 'Rradhitje zbritëse';
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

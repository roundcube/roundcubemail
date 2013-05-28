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
$labels['welcome']   = 'Fáilte go $product';
$labels['username']  = 'Úsáideoir';
$labels['password']  = 'Pasfhocal';
$labels['server']    = 'Freastalaí';
$labels['login']     = 'Logáil isteach';

// taskbar
$labels['logout']   = 'Logáil amach';
$labels['mail']     = 'Postas';
$labels['settings'] = 'Socruithe Pearsanta';
$labels['addressbook'] = 'Leabhair Seoltaí';

// mailbox names
$labels['inbox']  = 'Isteach';
$labels['drafts'] = 'Dréachtaí';
$labels['sent']   = 'Amach';
$labels['trash']  = 'Bosca bruscair';
$labels['junk']   = 'Dramhaíl';

// message listing
$labels['subject'] = 'Ábhair';
$labels['from']    = 'Seolaí';
$labels['sender']  = 'Sender';
$labels['to']      = 'Faighteoir';
$labels['cc']      = 'Cóipeáil';
$labels['bcc']     = 'Cóip cheilte';
$labels['replyto'] = 'Freagair go';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'Dáta';
$labels['size']    = 'Méid';
$labels['priority'] = 'Tosaíocht';
$labels['organization'] = 'Eagraíocht';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'Fillteán';
$labels['messagesfromto'] = 'Teachtaireachta as $from go $to as $count';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'Teachtaireacht $nr as $count';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'Cóipeáil';
$labels['move']     = 'Bog';
$labels['moveto']   = 'bog go...';
$labels['download'] = 'síos-luchtú';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'Comhad';
$labels['filesize'] = 'Toirt';

$labels['addtoaddressbook'] = 'Sábháil i mo leabhair seoltaí';

// weekdays short
$labels['sun'] = 'Do';
$labels['mon'] = 'Lu';
$labels['tue'] = 'Mh';
$labels['wed'] = 'Ch';
$labels['thu'] = 'Dé';
$labels['fri'] = 'Ao';
$labels['sat'] = 'Sa';

// weekdays long
$labels['sunday']    = 'Domhnach';
$labels['monday']    = 'An Luan';
$labels['tuesday']   = 'An Mháirt';
$labels['wednesday'] = 'An Chéadaoin';
$labels['thursday']  = 'An Déardaoin';
$labels['friday']    = 'Aoine';
$labels['saturday']  = 'An Satharn';

// months short
$labels['jan']	= 'Ean';
$labels['feb']	= 'Fea';
$labels['mar']	= 'Már';
$labels['apr']	= 'Aib';
$labels['may']	= 'Bea';
$labels['jun']	= 'Mei';
$labels['jul'] 	= 'Iúi';
$labels['aug']	= 'Lún';
$labels['sep']	= 'Sep';
$labels['oct']	= 'Oct';
$labels['nov']	= 'Sam';
$labels['dec']	= 'Nol';

// months long
$labels['longjan']	= 'Eanáir';
$labels['longfeb']	= 'Feabhra';
$labels['longmar']	= 'Márta';
$labels['longapr']	= 'Aibreán';
$labels['longmay']	= 'Bea';
$labels['longjun']	= 'Meitheamh';
$labels['longjul']	= 'Iúil';
$labels['longaug']	= 'Lúnasa';
$labels['longsep']	= 'Meán Fómhair';
$labels['longoct']	= 'Deireadh Fómhair';
$labels['longnov']	= 'Samhain';
$labels['longdec']	= 'Nollag';

$labels['today'] = 'Inniu';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'Seiceáil chun teachtaireacht nua';
$labels['compose']          = 'Scríobh teachtaireacht';
$labels['writenewmessage']  = 'Scríobh teachtaireacht nua';
$labels['reply']            = 'Reply';
$labels['replytomessage']   = 'Scríobh freagra chun an seolaí';
$labels['replytoallmessage'] = 'Scríobh freagra chun an seolaí agus na faighteoir';
$labels['replyall']         = 'Freagra gach';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'Seol ar aghaidh an teachtaireacht';
$labels['deletemessage']    = 'Chuir an teachtaireacht i mo bosca bruscair';
$labels['movemessagetotrash'] = 'Move message to trash';
$labels['printmessage']     = 'Priontáil an teachtaireacht seo';
$labels['previousmessage']  = 'Taispeáin an teachtaireacht roimhe seo';
$labels['firstmessage']     = 'Taispeáin an cead teachtaireacht';
$labels['nextmessage']      = 'Taispeáin an teachtaireacht ina dhiaidh sin';
$labels['lastmessage']      = 'Taispeáin an teachtaireacht deireanach';
$labels['backtolist']       = 'Téigh ar ais go dtí an liosta teachtaireachta';
$labels['viewsource']       = 'Éiriú bunchód';
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

$labels['select'] = 'Togh';
$labels['all'] = 'An uile';
$labels['none'] = 'Aon cheann';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'Na rudaí nach bhuil corraithe';
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
$labels['nonesort'] = 'Aon cheann';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'Comhbhrúigh';
$labels['empty'] = 'Folmhaigh';

$labels['quota'] = 'Toilleadh diosca atá athláimhe';
$labels['unknown']  = 'gan aithne';
$labels['unlimited']  = 'gan teorainn';

$labels['quicksearch']  = 'Mearcuardaigh';
$labels['resetsearch']  = 'Athshocraigh an cuardach';
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
$labels['sendmessage']    = 'Seol mo teachtaireacht láithreach bonn';
$labels['savemessage']    = 'Sábháil an dréachta seo';
$labels['addattachment']  = 'Suimigh Iatán';
$labels['charset']        = 'Foireann carachtar';
$labels['editortype']     = 'Sort eagarthóir';
$labels['returnreceipt']  = 'Admháil léite';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'Fiosrú ar mo litriú';
$labels['resumeediting'] = 'Athdhúisigh eagarthóireacht';
$labels['revertto']      = 'Filleadh ar';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'Iatání';
$labels['upload'] = 'Aistriú suas';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'Dún';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'Íseal';
$labels['lowest']  = 'Is Ísle';
$labels['normal']  = 'Gnách';
$labels['high']    = 'Ard';
$labels['highest'] = 'Is Airde';

$labels['nosubject']  = '(Níl aon Ábhair)';
$labels['showimages'] = 'Taispeáin na híomhánna';
$labels['alwaysshow'] = 'Always show images from $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Téacs amháin';
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
$labels['name']         = 'Leasainm';
$labels['firstname']    = 'Ainm baiste';
$labels['surname']      = 'Sloinne';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'Ríomhphost';
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
$labels['addcontact'] = 'Sábháil duine nua';
$labels['editcontact'] = 'Chuir chara in eagar';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'Athraigh';
$labels['cancel'] = 'Cealaigh';
$labels['save']   = 'Sábháil';
$labels['delete'] = 'Scrios';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'Scríobh carta teagmháil nua';
$labels['deletecontact']  = 'Scrios na daoine seo';
$labels['composeto']      = 'Scríobh teachtaireacht go';
$labels['contactsfromto'] = 'Daoine as $from to $to as $count';
$labels['print']          = 'Priontáil';
$labels['export']         = 'Easportáil';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Taispeáin an foireann roimhe seo';
$labels['firstpage']      = 'Taispeáin an céad foireann';
$labels['nextpage']       = 'Taispeáin an foireann ina dhiaidh sin';
$labels['lastpage']       = 'Taispeáin an foireann deireanach';

$labels['group'] = 'Group';
$labels['groups'] = 'Grúpaí';
$labels['personaladrbook'] = 'Seoltaí Pearsanta';

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
$labels['settingsfor'] = 'Socruithe chun';
$labels['about'] = 'About';
$labels['preferences'] = 'Tosaíocht';
$labels['userpreferences'] = 'Tosaíocht Pearsanta';
$labels['editpreferences'] = 'Athraigh tosaíocht pearsanta';

$labels['identities'] = 'Céannachtaí';
$labels['manageidentities'] = 'Stiúir céannachtaí i cuntas seo';
$labels['newidentity'] = 'Céannacht nua';

$labels['newitem'] = 'Mír nua';
$labels['edititem'] = 'Athraigh mhír';

$labels['preferhtml'] = 'Taispeáin an HTML';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'Teachtaireacht HTML';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'Dátaí Dheas';
$labels['setdefault']  = 'Socraigh an réamhshocraithe';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'Teanga';
$labels['timezone']  = 'Crios ama';
$labels['pagesize']  = 'Rónna in aghaidh gach leathanach';
$labels['signature'] = 'Mana clabhsúir';
$labels['dstactive']  = 'Laethúil um shamhradh';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Scríobh teachtaireachta HTML';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'Mana clabhsúir HTML';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'Taispeáin an pána réamhamharc';
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
$labels['autosavedraft']  = 'Sábháil dréachta go huathoibríoch';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'riamh';
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

$labels['folder']  = 'Fillteán';
$labels['folders']  = 'Fillteán';
$labels['foldername']  = 'Ainm fillteán';
$labels['subscribed']  = 'Síntiú';
$labels['messagecount'] = 'Messages';
$labels['create']  = 'Cruthaigh';
$labels['createfolder']  = 'Cruthaigh fillteán nua';
$labels['managefolders']  = 'Stiúir na fillteán';
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

$labels['sortby'] = 'Sórtáil trí';
$labels['sortasc']  = 'Sórtáil in ord méadaitheach';
$labels['sortdesc'] = 'Sórtáil in ord neartaitheach';
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

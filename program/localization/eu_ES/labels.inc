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
$labels['welcome']   = 'Ongi etorri $product';
$labels['username']  = 'Erabiltzailea';
$labels['password']  = 'Pasahitza';
$labels['server']    = 'Zerbitzaria';
$labels['login']     = 'Saio hasiera';

// taskbar
$labels['logout']   = 'Irten';
$labels['mail']     = 'ePosta';
$labels['settings'] = 'Ezarpen Pertsonalak';
$labels['addressbook'] = 'Helbide Liburua';

// mailbox names
$labels['inbox']  = 'Sarrera';
$labels['drafts'] = 'Zirriborroak';
$labels['sent']   = 'Bidalitakoak';
$labels['trash']  = 'Zakarrontzia';
$labels['junk']   = 'Zabor-posta';

// message listing
$labels['subject'] = 'Gaia';
$labels['from']    = 'Nork';
$labels['sender']  = 'Igorlea';
$labels['to']      = 'Nori';
$labels['cc']      = 'Kopia';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Erantzun-Honi';
$labels['followupto'] = 'Jarraipena honi';
$labels['date']    = 'Data';
$labels['size']    = 'Tamaina';
$labels['priority'] = 'Lehentasuna';
$labels['organization'] = 'Erakundea';
$labels['readstatus'] = 'Irakurketaren egoera';
$labels['listoptions'] = 'Zerrendaren aukerak...';

$labels['mailboxlist'] = 'Karpetak';
$labels['messagesfromto'] = '$from -tik $to -ra $count mezuetatik';
$labels['threadsfromto'] = '$from -etik $to -rako gaiak (guztira $count)';
$labels['messagenrof'] = '$nr mezua $count -tik';
$labels['fromtoshort'] = '$from - $to , guztira $count';

$labels['copy']     = 'Kopia';
$labels['move']     = 'Mugitu';
$labels['moveto']   = 'mugitu hona...';
$labels['download'] = 'deskargatu';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'Fitxategi Izena';
$labels['filesize'] = 'Fitxategi Tamaina';

$labels['addtoaddressbook'] = 'Helbide liburura gehitu';

// weekdays short
$labels['sun'] = 'Iga';
$labels['mon'] = 'Al';
$labels['tue'] = 'As';
$labels['wed'] = 'Azt';
$labels['thu'] = 'Og';
$labels['fri'] = 'Or';
$labels['sat'] = 'Lar';

// weekdays long
$labels['sunday']    = 'Igandea';
$labels['monday']    = 'Astelehena';
$labels['tuesday']   = 'Asteartea';
$labels['wednesday'] = 'Asteazkena';
$labels['thursday']  = 'Osteguna';
$labels['friday']    = 'Ostirala';
$labels['saturday']  = 'Larunbata';

// months short
$labels['jan']	= 'Urt';
$labels['feb']	= 'Ots';
$labels['mar']	= 'Mar';
$labels['apr']	= 'Api';
$labels['may']	= 'Maiatza';
$labels['jun']	= 'Eka';
$labels['jul'] 	= 'Uzt';
$labels['aug']	= 'Abu';
$labels['sep']	= 'Ira';
$labels['oct']	= 'Urr';
$labels['nov']	= 'Aza';
$labels['dec']	= 'Abe';

// months long
$labels['longjan']	= 'Urtarrila';
$labels['longfeb']	= 'Otsaila';
$labels['longmar']	= 'Martxoa';
$labels['longapr']	= 'Apirila';
$labels['longmay']	= 'Maiatza';
$labels['longjun']	= 'Ekaina';
$labels['longjul']	= 'Uztaila';
$labels['longaug']	= 'Abuztua';
$labels['longsep']	= 'Iraila';
$labels['longoct']	= 'Urria';
$labels['longnov']	= 'Azaroa';
$labels['longdec']	= 'Abendua';

$labels['today'] = 'Gaur';

// toolbar buttons
$labels['refresh']          = 'Eguneratu';
$labels['checkmail']        = 'Mezu berrien bila arakatu';
$labels['compose']          = 'Mezu bat sortu';
$labels['writenewmessage']  = 'Mezu berri bat sortu';
$labels['reply']            = 'Erantzun';
$labels['replytomessage']   = 'Mezuari erantzun';
$labels['replytoallmessage'] = 'Bidaltzaile eta hartzaile guztiei erantzun';
$labels['replyall']         = 'Guztiei erantzun';
$labels['replylist']        = 'Erantzun zerrenda';
$labels['forward']          = 'Berbidali';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Erantsi gisa berbidali';
$labels['forwardmessage']   = 'Mezua Birbidali';
$labels['deletemessage']    = 'Mezua ezabatu';
$labels['movemessagetotrash'] = 'Mezua zakarrontzira mugitu';
$labels['printmessage']     = 'Mezu hau inprimatu';
$labels['previousmessage']  = 'Aurreko mezua ikusi';
$labels['firstmessage']     = 'Lehenengo mezua ikusi';
$labels['nextmessage']      = 'Hurrengo mezua ikusi';
$labels['lastmessage']      = 'Azken mezua ikusi';
$labels['backtolist']       = 'Mezu zerrendara itzuli';
$labels['viewsource']       = 'Jatorria bistarazi';
$labels['mark']             = 'Markatu';
$labels['markmessages']     = 'Markatu mezuak';
$labels['markread']         = 'Irakurria bezala';
$labels['markunread']       = 'Ez-irakurria bezala';
$labels['markflagged']      = 'Markatua bezala';
$labels['markunflagged']    = 'Ez-markatua bezala';
$labels['moreactions']      = 'Ekintza gehiago...';
$labels['more']             = 'Gehiago';
$labels['back']             = 'Atzera';
$labels['options']          = 'Aukerak';

$labels['select'] = 'Hautatu';
$labels['all'] = 'Denak';
$labels['none'] = 'Bat ere ez';
$labels['currpage'] = 'Uneko orrialdea';
$labels['unread'] = 'Irakurri gabeak';
$labels['flagged'] = 'Banderaduna';
$labels['unanswered'] = 'Unanswered';
$labels['deleted'] = 'Ezabatuak';
$labels['undeleted'] = 'Ezabatu gabeak';
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
$labels['fromto'] = 'Nork/Nori';
$labels['flag'] = 'Flag';
$labels['attachment'] = 'Attachment';
$labels['nonesort'] = 'Bat ere ez';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'Trinkotu';
$labels['empty'] = 'Hustu';

$labels['quota'] = 'Disko erabilera';
$labels['unknown']  = 'ezezaguna';
$labels['unlimited']  = 'mugagabea';

$labels['quicksearch']  = 'Bilaketa azkarra';
$labels['resetsearch']  = 'Bilaketa berrabiarazi';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Entire message';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Lehio berrian ireki';
$labels['emlsave'] = 'Download (.eml)';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'Mezua orain bidali';
$labels['savemessage']    = 'Zirriborro hau gorde';
$labels['addattachment']  = 'Fitxategia erantsi';
$labels['charset']        = 'Karaktere jokoa';
$labels['editortype']     = 'Editore mota';
$labels['returnreceipt']  = 'Bidaltzaileari itzuli';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Jatorrizko mezua';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'Egiaztaketa ortografikoa';
$labels['resumeediting'] = 'Editatzen jarraitu';
$labels['revertto']      = 'Itzuli hona';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'Eranskinak';
$labels['upload'] = 'Igo';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'Itxi';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'Baxua';
$labels['lowest']  = 'Baxuena';
$labels['normal']  = 'Normala';
$labels['high']    = 'Altua';
$labels['highest'] = 'Altuena';

$labels['nosubject']  = '(gairik gabea)';
$labels['showimages'] = 'Irudiak bistarazi';
$labels['alwaysshow'] = 'Beti ikusi $sender -en irudiak';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Testu laua';
$labels['savesentmessagein'] = 'Gorde mezua hemen';
$labels['dontsave'] = 'ez gorde';
$labels['maxuploadsize'] = 'Maximum allowed file size is $size';

$labels['addcc'] = 'Gehitu Cc (kopia)';
$labels['addbcc'] = 'Gehitu Bcc (kopia ezkutua)';
$labels['addreplyto'] = 'Gehitu erantzun-honi';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'Mezu honen bidaltzaileak mezua irakurtzen duzunean jakinaraztea eskatu du. Bidaltzailea ohartu nahi duzu?';
$labels['receiptread'] = 'Itzulketa agiria (irakurria)';
$labels['yourmessage'] = 'Hau zure mezuaren Itzulketa Agiria da';
$labels['receiptnote'] = 'Oharra: Agiri honek hartzailearen ordenagailuan mezua ikusi dala adierazten du besterik gabe. Honek ez du esan nahi hartzaileak mezua irakurri edo/eta ulertu duenik:';

// address boook
$labels['name']         = 'Izena bistarazi';
$labels['firstname']    = 'Izena';
$labels['surname']      = 'Abizena';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Ezizena';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'ePosta';
$labels['phone']        = 'Phone';
$labels['address']      = 'Helbidea';
$labels['street']       = 'Kalea';
$labels['locality']     = 'City';
$labels['zipcode']      = 'Posta kodea';
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
$labels['typemobile']  = 'Mugikorra';
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
$labels['addcontact'] = 'Txartel berria gehitu';
$labels['editcontact'] = 'Txartela editatu';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Informazio pertsonala';

$labels['edit']   = 'Editatu';
$labels['cancel'] = 'Utzi';
$labels['save']   = 'Gorde';
$labels['delete'] = 'Ezabatu';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'Helbide liburu txartel berria sortu';
$labels['deletecontact']  = 'Aukeraturiko txartelak ezabatu';
$labels['composeto']      = 'Bidali mezua honi:';
$labels['contactsfromto'] = '$from -tik $to -ra $count txarteletatik';
$labels['print']          = 'Inprimatu';
$labels['export']         = 'Esportatu';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Aurreko sorta ikusi';
$labels['firstpage']      = 'Lehenengo sorta ikusi';
$labels['nextpage']       = 'Hurrengo sorta ikusi';
$labels['lastpage']       = 'Azken sorta ikusi';

$labels['group'] = 'Group';
$labels['groups'] = 'Taldeak';
$labels['personaladrbook'] = 'Helbide pertsonalak';

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
$labels['settingsfor'] = 'Honen ezarpenak:';
$labels['about'] = 'About';
$labels['preferences'] = 'Hobespenak';
$labels['userpreferences'] = 'Erabiltzaile hobespenak';
$labels['editpreferences'] = 'Erabiltzaile hobespenak editatu';

$labels['identities'] = 'Identitateak';
$labels['manageidentities'] = 'Kontu honetako identitateak kudeatu';
$labels['newidentity'] = 'Identitate berria';

$labels['newitem'] = 'Elementu berria';
$labels['edititem'] = 'Elementua Editatu';

$labels['preferhtml'] = 'HTML nahiago';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'HTML Mezua';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'Data politak';
$labels['setdefault']  = 'Lehenespenak ezarri';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'Hizkuntza';
$labels['timezone']  = 'Ordu zonaldea';
$labels['pagesize']  = 'Lerro orrialdeko';
$labels['signature'] = 'Sinadura';
$labels['dstactive']  = 'Eguneko gordeketak';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'HTML mezuak sortu';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'HTML sinadura';
$labels['previewpane'] = 'Aurrebista panela ikusi';
$labels['skin'] = 'Azal-interfazea';
$labels['logoutclear'] = 'Garbitu Zakarronzia irtetean';
$labels['logoutcompact'] = 'Trinkotu Sarrera irtetean';
$labels['uisettings'] = 'Erabiltzaile-interfazea';
$labels['serversettings'] = 'Zerbitzariaren ezarpenak';
$labels['mailboxview'] = 'Postakutxatilaren ikuspegia';
$labels['mdnrequests'] = 'Bidaltzailearen jakinarazpena';
$labels['askuser'] = 'galdetu erabiltzaileari';
$labels['autosend'] = 'bidali automatikoki';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'ezikusi';
$labels['readwhendeleted'] = 'Markatu mezua irakurrita bezala ezabatzerakoan';
$labels['flagfordeletion'] = 'Markatu mezua ezabatzeko ezabatu beharrean';
$labels['skipdeleted'] = 'Do not show deleted messages';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'from known senders';
$labels['always'] = 'always';
$labels['showinlineimages'] = 'Erakutsi erantzitako irudiak mezuaren azpian';
$labels['autosavedraft']  = 'Zirriborroa automatikoki gorde';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'inoiz ez';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'Mezuak ikusi';
$labels['messagescomposition'] = 'Mezuak sortu';
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

$labels['folder']  = 'Karpeta';
$labels['folders']  = 'Karpetak';
$labels['foldername']  = 'Karpeta izena';
$labels['subscribed']  = 'Harpideturik';
$labels['messagecount'] = 'Mezuak';
$labels['create']  = 'Sortu';
$labels['createfolder']  = 'Karpeta berria sortu';
$labels['managefolders']  = 'Karpeta kudeaketa';
$labels['specialfolders'] = 'Karpeta bereziak';
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

$labels['sortby'] = 'Honela ordenatu';
$labels['sortasc']  = 'Goraka ordenatu';
$labels['sortdesc'] = 'Beheraka ordenatu';
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

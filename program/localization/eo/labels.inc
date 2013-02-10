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
$labels['welcome']   = 'Bonvenon al $product';
$labels['username']  = 'Uzantnomo';
$labels['password']  = 'Pasvorto';
$labels['server']    = 'Servilo';
$labels['login']     = 'Ensaluti';

// taskbar
$labels['logout']   = 'Elsaluti';
$labels['mail']     = 'Retpoŝto';
$labels['settings'] = 'Agordoj';
$labels['addressbook'] = 'Adresaro';

// mailbox names
$labels['inbox']  = 'Ricevitujo';
$labels['drafts'] = 'Malnetujo';
$labels['sent']   = 'Senditujo';
$labels['trash']  = 'Rubujo';
$labels['junk']   = 'Spamujo';

// message listing
$labels['subject'] = 'Temo';
$labels['from']    = 'Sendanto';
$labels['sender']  = 'Sender';
$labels['to']      = 'Ricevanto';
$labels['cc']      = 'Kopio';
$labels['bcc']     = 'Blindkopio';
$labels['replyto'] = 'Respondu-Al';
$labels['followupto'] = 'Respondo-Al';
$labels['date']    = 'Dato';
$labels['size']    = 'Grando';
$labels['priority'] = 'Prioritato';
$labels['organization'] = 'Organizo';
$labels['readstatus'] = 'Legis statuson';
$labels['listoptions'] = 'List-agordoj...';

$labels['mailboxlist'] = 'Dosierujoj';
$labels['messagesfromto'] = 'Mesaĝoj ekde $from ĝis $to de $count';
$labels['threadsfromto'] = 'Fadenoj $from al $to de $count';
$labels['messagenrof'] = 'Mesaĝo $nr-a de $count';
$labels['fromtoshort'] = '$from - $to de $count';

$labels['copy']     = 'Kopii';
$labels['move']     = 'Movi';
$labels['moveto']   = 'transloku al...';
$labels['download'] = 'elŝutu';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'Dosiernomo';
$labels['filesize'] = 'Dosiergrando';

$labels['addtoaddressbook'] = 'Aldonu al adresaro';

// weekdays short
$labels['sun'] = 'Dim';
$labels['mon'] = 'Lun';
$labels['tue'] = 'Mar';
$labels['wed'] = 'Mer';
$labels['thu'] = 'Ĵaŭ';
$labels['fri'] = 'Ven';
$labels['sat'] = 'Sab';

// weekdays long
$labels['sunday']    = 'Dimanĉo';
$labels['monday']    = 'Lundo';
$labels['tuesday']   = 'Mardo';
$labels['wednesday'] = 'Merkredo';
$labels['thursday']  = 'Ĵaŭdo';
$labels['friday']    = 'Vendredo';
$labels['saturday']  = 'Sabato';

// months short
$labels['jan']	= 'Jan';
$labels['feb']	= 'Feb';
$labels['mar']	= 'Mar';
$labels['apr']	= 'Apr';
$labels['may']	= 'Majo';
$labels['jun']	= 'Jun';
$labels['jul'] 	= 'Jul';
$labels['aug']	= 'Aŭg';
$labels['sep']	= 'Sep';
$labels['oct']	= 'Okt';
$labels['nov']	= 'Nov';
$labels['dec']	= 'Dec';

// months long
$labels['longjan']	= 'Januaro';
$labels['longfeb']	= 'Februaro';
$labels['longmar']	= 'Marto';
$labels['longapr']	= 'Aprilo';
$labels['longmay']	= 'Majo';
$labels['longjun']	= 'Junio';
$labels['longjul']	= 'Julio';
$labels['longaug']	= 'Aŭgusto';
$labels['longsep']	= 'Septembro';
$labels['longoct']	= 'Oktobro';
$labels['longnov']	= 'Novembro';
$labels['longdec']	= 'Decembro';

$labels['today'] = 'Hodiaŭ';

// toolbar buttons
$labels['refresh']          = 'Aktualigi';
$labels['checkmail']        = 'Kontroli por novaj mesaĝoj';
$labels['compose']          = 'Verki';
$labels['writenewmessage']  = 'Verki novan mesaĝon';
$labels['reply']            = 'Respondi';
$labels['replytomessage']   = 'Respondi al sendanto';
$labels['replytoallmessage'] = 'Respondi al listo aŭ al sendanto kaj al ĉiuj ricevintoj';
$labels['replyall']         = 'Respondi al ĉiuj';
$labels['replylist']        = 'Respondi al listo';
$labels['forward']          = 'Plusendi';
$labels['forwardinline']    = 'Plusendu enmesaĝe';
$labels['forwardattachment'] = 'Plusendu alfikse';
$labels['forwardmessage']   = 'Plusendu mesaĝon';
$labels['deletemessage']    = 'Forigu mesaĝon';
$labels['movemessagetotrash'] = 'Transloku mesaĝon al rubujo';
$labels['printmessage']     = 'Printu ĉi tiun mesaĝon';
$labels['previousmessage']  = 'Montru antaŭan mesaĝon';
$labels['firstmessage']     = 'Montru unuan mesaĝon';
$labels['nextmessage']      = 'Montru sekvan mesaĝon';
$labels['lastmessage']      = 'Montru lastan mesaĝon';
$labels['backtolist']       = 'Reen al mesaĝo-listo';
$labels['viewsource']       = 'Montri fonton';
$labels['mark']             = 'Marki';
$labels['markmessages']     = 'Marki mesaĝojn';
$labels['markread']         = 'Kiel legita';
$labels['markunread']       = 'Kiel nelegita';
$labels['markflagged']      = 'kiel markita';
$labels['markunflagged']    = 'kiel nemarkita';
$labels['moreactions']      = 'Pliaj agoj';
$labels['more']             = 'Pli';
$labels['back']             = 'Malantaŭen';
$labels['options']          = 'Agordoj';

$labels['select'] = 'Elektu';
$labels['all'] = 'Ĉion';
$labels['none'] = 'Nenion';
$labels['currpage'] = 'Nuna paĝo';
$labels['unread'] = 'Nelegitan';
$labels['flagged'] = 'Markita';
$labels['unanswered'] = 'Nerespondita';
$labels['deleted'] = 'Forigita';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Inversigu';
$labels['filter'] = 'Filtru';
$labels['list'] = 'Listo';
$labels['threads'] = 'Fadenoj';
$labels['expand-all'] = 'Malfaldi ĉion';
$labels['expand-unread'] = 'Malfaldi Nelegitajn';
$labels['collapse-all'] = 'Faldi ĉion';
$labels['threaded'] = 'Fadenita';

$labels['autoexpand_threads'] = 'Malfaldi mesaĝ-fadenojn';
$labels['do_expand'] = 'Ĉiuj fadenoj';
$labels['expand_only_unread'] = 'Nur kun nelegitaj mesaĝoj';
$labels['fromto'] = 'De/Al';
$labels['flag'] = 'Marko';
$labels['attachment'] = 'Aldonaĵo';
$labels['nonesort'] = 'Nenion';
$labels['sentdate'] = 'Send-dato';
$labels['arrival'] = 'Ricev-dato';
$labels['asc'] = 'kreskante';
$labels['desc'] = 'malkreskante';
$labels['listcolumns'] = 'List-rubrikoj';
$labels['listsorting'] = 'Ordig-rubriko';
$labels['listorder'] = 'Ordig-ordo';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Dosierujo-agoj';
$labels['compact'] = 'Kompakta';
$labels['empty'] = 'Malplena';

$labels['quota'] = 'Diskuzo';
$labels['unknown']  = 'nekonata';
$labels['unlimited']  = 'senlima';

$labels['quicksearch']  = 'Rapida serĉo';
$labels['resetsearch']  = 'Vakigu serĉon';
$labels['searchmod']  = 'Serĉ-opcioj';
$labels['msgtext']  = 'Tuta mesaĝo';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Malfermu en nova fenestro';
$labels['emlsave'] = 'Elŝutu (.eml)';

// message compose
$labels['editasnew']      = 'Redaktu kiel nova';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'Sendu mesaĝon';
$labels['savemessage']    = 'Savu ĉi tiun malneton';
$labels['addattachment']  = 'Alfiksu dosieron';
$labels['charset']        = 'Karaktraro';
$labels['editortype']     = 'Speco de redaktilo';
$labels['returnreceipt']  = 'Return receipt';
$labels['dsn']            = 'Ricev-status-konfirmo';
$labels['mailreplyintro'] = 'Je $date, $sender skribis:';
$labels['originalmessage'] = 'Originala mesaĝo';

$labels['editidents']    = 'Redaktu identojn';
$labels['spellcheck']    = 'Literumu';
$labels['checkspelling'] = 'Kontrolu literumadon';
$labels['resumeediting'] = 'Rekomencu redaktadon';
$labels['revertto']      = 'Reiru al';

$labels['attach'] = 'Alfiksu';
$labels['attachments'] = 'Alfiksaĵoj';
$labels['upload'] = 'Alŝutu';
$labels['uploadprogress'] = '$percent ($current de $total)';
$labels['close']  = 'Fermi';
$labels['messageoptions']  = 'Mesaĝ-agordoj...';

$labels['low']     = 'Malalta';
$labels['lowest']  = 'Plej malalta';
$labels['normal']  = 'Normala';
$labels['high']    = 'Alta';
$labels['highest'] = 'Plej alta';

$labels['nosubject']  = '(sen temo)';
$labels['showimages'] = 'Montru bildojn';
$labels['alwaysshow'] = 'Ĉiam montru bildojn de $sender';
$labels['isdraft']    = 'Ĉi-mesaĝo estas malneto';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Plata teksto';
$labels['savesentmessagein'] = 'Konservu senditan mesaĝon en';
$labels['dontsave'] = 'Ne konservu';
$labels['maxuploadsize'] = 'Maksimuma permesata dosier-grando estas $size';

$labels['addcc'] = 'Aldonu kopi-ricevanton';
$labels['addbcc'] = 'Aldonu blindan kopi-ricevanton';
$labels['addreplyto'] = 'Aldonu Respondu-Al';
$labels['addfollowupto'] = 'Aldonu Respondu-Al';

// mdn
$labels['mdnrequest'] = 'La sendinto de ĉi tiu mesaĝo petis sciigon de kiam vi legas ĝin. Ĉu volas sciigi la sendinton?';
$labels['receiptread'] = 'Legokonfirmo';
$labels['yourmessage'] = 'Jen estas legokonfirmo de via mesaĝo';
$labels['receiptnote'] = 'Notu: Ĉi tiu konfirmo nur asertas ke la mesaĝo estis malfermita je la komputilo de la ricevinto. Ne estas garantio ke la ricevinto legis aŭ komprenis la enhavon.';

// address boook
$labels['name']         = 'Nomo';
$labels['firstname']    = 'Antaŭnomo';
$labels['surname']      = 'Familia nomo';
$labels['middlename']   = 'Meza Nomo';
$labels['nameprefix']   = 'Prefikso';
$labels['namesuffix']   = 'Sufikso';
$labels['nickname']     = 'Kromnomo';
$labels['jobtitle']     = 'Postena titolo';
$labels['department']   = 'Departemento';
$labels['gender']       = 'Sekso';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'Retpoŝto';
$labels['phone']        = 'Telefono';
$labels['address']      = 'Adreso';
$labels['street']       = 'Strato';
$labels['locality']     = 'Urbo';
$labels['zipcode']      = 'Poŝt-kodo';
$labels['region']       = 'Ŝtato/Provinco';
$labels['country']      = 'Lando';
$labels['birthday']     = 'Naskiĝdato';
$labels['anniversary']  = 'Datreveno';
$labels['website']      = 'Retejo';
$labels['instantmessenger'] = 'TM';
$labels['notes'] = 'Notoj';
$labels['male']   = 'viro';
$labels['female'] = 'virino';
$labels['manager'] = 'Estro';
$labels['assistant'] = 'Asistanto';
$labels['spouse'] = 'Edz(in)o';
$labels['allfields'] = 'Ĉiuj kampoj';
$labels['search'] = 'Serĉu';
$labels['advsearch'] = 'Detala sercô';
$labels['advanced'] = 'Altnivela';
$labels['other'] = 'Alia';

$labels['typehome']   = 'Hejmo';
$labels['typework']   = 'Laboro';
$labels['typeother']  = 'Alia';
$labels['typemobile']  = 'Poŝtelefono';
$labels['typemain']  = 'Ĉefa';
$labels['typehomefax']  = 'Hejma faksilo';
$labels['typeworkfax']  = 'Laboreja faksilo';
$labels['typecar']  = 'Aŭtomobilo';
$labels['typepager']  = 'Televokilo';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Asistanto';
$labels['typehomepage']  = 'Hejmpaĝo';
$labels['typeblog'] = 'Blogo';
$labels['typeprofile'] = 'Profilo';

$labels['addfield'] = 'Aldonu kampon...';
$labels['addcontact'] = 'Aldonu kontakton';
$labels['editcontact'] = 'Redaktu kontakton';
$labels['contacts'] = 'Kontaktoj';
$labels['contactproperties'] = 'Kontakt-informoj';
$labels['personalinfo'] = 'Personaj informoj';

$labels['edit']   = 'Redaktu';
$labels['cancel'] = 'Nuligi';
$labels['save']   = 'Konservi';
$labels['delete'] = 'Forigi';
$labels['rename'] = 'Alinomu';
$labels['addphoto'] = 'Aldonu';
$labels['replacephoto'] = 'Anstataŭu';
$labels['uploadphoto'] = 'Alŝutu bildon';

$labels['newcontact']     = 'Kreu novan kontakton';
$labels['deletecontact']  = 'Forigu elektatajn kontaktojn';
$labels['composeto']      = 'Verku mesaĝon al';
$labels['contactsfromto'] = 'Kontaktoj ekde $from ĝis $to de $count';
$labels['print']          = 'Presi';
$labels['export']         = 'Elporti';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Montru auntaŭan';
$labels['firstpage']      = 'Montru unuan';
$labels['nextpage']       = 'Montru sekvan';
$labels['lastpage']       = 'Montru lastan';

$labels['group'] = 'Group';
$labels['groups'] = 'Grupoj';
$labels['personaladrbook'] = 'Persona Adresoj';

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
$labels['settingsfor'] = 'Preferoj de';
$labels['about'] = 'About';
$labels['preferences'] = 'Preferoj';
$labels['userpreferences'] = 'Uzant-preferoj';
$labels['editpreferences'] = 'Redaktu uzant-preferojn';

$labels['identities'] = 'Identoj';
$labels['manageidentities'] = 'Administru identojn de ĉi tiu konto';
$labels['newidentity'] = 'Nova idento';

$labels['newitem'] = 'Nova ero';
$labels['edititem'] = 'Redaktu eron';

$labels['preferhtml'] = 'Montru HTML';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'HTML Mesaĝo';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'Agrablaj Datoj';
$labels['setdefault']  = 'Agordu kiel norma';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'Lingvo';
$labels['timezone']  = 'Tempzono';
$labels['pagesize']  = 'Mesaĝoj po paĝo';
$labels['signature'] = 'Subskribo';
$labels['dstactive']  = 'Taglumsavado';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Uzado de HTML-redaktilo';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'HTML subskribo';
$labels['previewpane'] = 'Montru antaŭrigardejon';
$labels['skin'] = 'Interfaco-etoso';
$labels['logoutclear'] = 'Malplenigu rubujon je eliro';
$labels['logoutcompact'] = 'Kompaktigu ricevitujon je eliro';
$labels['uisettings'] = 'Uzanto-interfaco';
$labels['serversettings'] = 'Servilo-agordo';
$labels['mailboxview'] = 'Ricevitujo vido';
$labels['mdnrequests'] = 'Sendanto-sciigoj';
$labels['askuser'] = 'demandu';
$labels['autosend'] = 'sendu aŭtomate';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'malatentu';
$labels['readwhendeleted'] = 'Marku mesaĝon kiel legita kiam forigata';
$labels['flagfordeletion'] = 'Marku mesaĝon kiel forigita anstataŭ forigi';
$labels['skipdeleted'] = 'Do not show deleted messages';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'from known senders';
$labels['always'] = 'always';
$labels['showinlineimages'] = 'Montru alfiksitajn bildojn sub la mesaĝo';
$labels['autosavedraft']  = 'Aŭtomate savu malneton';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'neniam';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'Mesaĝoj vidigante';
$labels['messagescomposition'] = 'Mesaĝo-verkado';
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

$labels['folder']  = 'Dosierujo';
$labels['folders']  = 'Dosierujoj';
$labels['foldername']  = 'Dosierujo-nomo';
$labels['subscribed']  = 'Abonata';
$labels['messagecount'] = 'Mesaĝoj';
$labels['create']  = 'Krei';
$labels['createfolder']  = 'Kreu novan dosierujon';
$labels['managefolders']  = 'Administru dosierujon';
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

$labels['sortby'] = 'Ordigu per';
$labels['sortasc']  = 'Ordigu kreskante';
$labels['sortdesc'] = 'Ordigu malkreskante';
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

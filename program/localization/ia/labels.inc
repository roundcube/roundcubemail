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
$labels['welcome']   = 'Benvenite a $product';
$labels['username']  = 'Nomine de usator';
$labels['password']  = 'Contrasigno';
$labels['server']    = 'Servitor';
$labels['login']     = 'Apertura de session';

// taskbar
$labels['logout']   = 'Clauder session';
$labels['mail']     = 'E-posta';
$labels['settings'] = 'Configurationes';
$labels['addressbook'] = 'Adressario';

// mailbox names
$labels['inbox']  = 'Cassa de entrata';
$labels['drafts'] = 'Drafts';
$labels['sent']   = 'Inviate';
$labels['trash']  = 'Trash';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Subjecto';
$labels['from']    = 'Ex';
$labels['sender']  = 'Expeditor';
$labels['to']      = 'A';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Responder a';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'Data';
$labels['size']    = 'Dimension';
$labels['priority'] = 'Prioritate';
$labels['organization'] = 'Organisation';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'Dossieres';
$labels['messagesfromto'] = 'Messages $from to $to of $count';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'Message $nr de $count';
$labels['fromtoshort'] = '$from – $to de $count';

$labels['copy']     = 'Copiar';
$labels['move']     = 'Mover';
$labels['moveto']   = 'Mover a';
$labels['download'] = 'Discargar';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Monstrar';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'Nomine de file';
$labels['filesize'] = 'Dimension del file';

$labels['addtoaddressbook'] = 'Adder al adressario';

// weekdays short
$labels['sun'] = 'dom';
$labels['mon'] = 'lun';
$labels['tue'] = 'mar';
$labels['wed'] = 'mer';
$labels['thu'] = 'jov';
$labels['fri'] = 'ven';
$labels['sat'] = 'sab';

// weekdays long
$labels['sunday']    = 'Dominica';
$labels['monday']    = 'Lunedi';
$labels['tuesday']   = 'Martedi';
$labels['wednesday'] = 'Mercuridi';
$labels['thursday']  = 'Jovedi';
$labels['friday']    = 'Venerdi';
$labels['saturday']  = 'Sabbato';

// months short
$labels['jan']	= 'Jan';
$labels['feb']	= 'Feb';
$labels['mar']	= 'Mar';
$labels['apr']	= 'Apr';
$labels['may']	= 'Mai';
$labels['jun']	= 'Jun';
$labels['jul'] 	= 'Jul';
$labels['aug']	= 'Aug';
$labels['sep']	= 'Sep';
$labels['oct']	= 'Oct';
$labels['nov']	= 'Nov';
$labels['dec']	= 'Dec';

// months long
$labels['longjan']	= 'januario';
$labels['longfeb']	= 'Februario';
$labels['longmar']	= 'Martio';
$labels['longapr']	= 'April';
$labels['longmay']	= 'Mai';
$labels['longjun']	= 'Junio';
$labels['longjul']	= 'Julio';
$labels['longaug']	= 'Augusto';
$labels['longsep']	= 'Septembre';
$labels['longoct']	= 'Octobre';
$labels['longnov']	= 'Novembre';
$labels['longdec']	= 'Decembre';

$labels['today'] = 'Hodie';

// toolbar buttons
$labels['refresh']          = 'Refrescar';
$labels['checkmail']        = 'Verificar nove messages';
$labels['compose']          = 'Componer';
$labels['writenewmessage']  = 'Crear un nove message';
$labels['reply']            = 'Responder';
$labels['replytomessage']   = 'Reply to sender';
$labels['replytoallmessage'] = 'Reply to list or to sender and all recipients';
$labels['replyall']         = 'Responder a totes';
$labels['replylist']        = 'Responder a lista';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'Forward the message';
$labels['deletemessage']    = 'Deler messsage';
$labels['movemessagetotrash'] = 'Move message to trash';
$labels['printmessage']     = 'Imprimer iste message';
$labels['previousmessage']  = 'Show previous message';
$labels['firstmessage']     = 'Show first message';
$labels['nextmessage']      = 'Show next message';
$labels['lastmessage']      = 'Show last message';
$labels['backtolist']       = 'Back to message list';
$labels['viewsource']       = 'Monstrar fonte';
$labels['mark']             = 'Marcar';
$labels['markmessages']     = 'Marcar messages';
$labels['markread']         = 'As read';
$labels['markunread']       = 'As unread';
$labels['markflagged']      = 'As flagged';
$labels['markunflagged']    = 'As unflagged';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'Plus';
$labels['back']             = 'Retornar';
$labels['options']          = 'Optiones';

$labels['select'] = 'Seliger';
$labels['all'] = 'Totes';
$labels['none'] = 'Necun';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'Non legite';
$labels['flagged'] = 'Marcate';
$labels['unanswered'] = 'Unanswered';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = 'Delete';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Inverter';
$labels['filter'] = 'Filtro';
$labels['list'] = 'Lista';
$labels['threads'] = 'Topicos';
$labels['expand-all'] = 'Expand All';
$labels['expand-unread'] = 'Expand Unread';
$labels['collapse-all'] = 'Collapse All';
$labels['threaded'] = 'Threaded';

$labels['autoexpand_threads'] = 'Expand message threads';
$labels['do_expand'] = 'all threads';
$labels['expand_only_unread'] = 'only with unread messages';
$labels['fromto'] = 'From/To';
$labels['flag'] = 'Marcar';
$labels['attachment'] = 'Attachamento';
$labels['nonesort'] = 'Necun';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Actiones de dossier...';
$labels['compact'] = 'Compacte';
$labels['empty'] = 'Vacue';

$labels['quota'] = 'Disk usage';
$labels['unknown']  = 'incognite';
$labels['unlimited']  = 'unlimited';

$labels['quicksearch']  = 'Cerca rapide';
$labels['resetsearch']  = 'Reinitialisar cerca';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Message complete';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Aperir in nove fenestra';
$labels['emlsave'] = 'Download (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'Modificar como nove';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'Inviar message';
$labels['savemessage']    = 'Salveguardar como version provisori';
$labels['addattachment']  = 'Attachar un file';
$labels['charset']        = 'Insimul de Characteres';
$labels['editortype']     = 'Typo de editor';
$labels['returnreceipt']  = 'Return receipt';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Message original';

$labels['editidents']    = 'Modificar identitates';
$labels['spellcheck']    = 'Orthographia';
$labels['checkspelling'] = 'Verificar orthographia';
$labels['resumeediting'] = 'Resume editing';
$labels['revertto']      = 'Revert to';

$labels['attach'] = 'Attachar';
$labels['attachments'] = 'Attachamentos';
$labels['upload'] = 'Incargar';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'Clauder';
$labels['messageoptions']  = 'Optiones de message...';

$labels['low']     = 'Basse';
$labels['lowest']  = 'Minimal';
$labels['normal']  = 'Normal';
$labels['high']    = 'Alte';
$labels['highest'] = 'Maximal';

$labels['nosubject']  = '(sin subjecto)';
$labels['showimages'] = 'Monstrar imagines';
$labels['alwaysshow'] = 'Always show images from $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Texto plan';
$labels['savesentmessagein'] = 'Salveguardar message inviate in';
$labels['dontsave'] = 'non salveguardar';
$labels['maxuploadsize'] = 'Le dimension maximal de iste file es $size';

$labels['addcc'] = 'Adder Cc';
$labels['addbcc'] = 'Adder Bcc';
$labels['addreplyto'] = 'Add Reply-To';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'The sender of this message has asked to be notified when you read this message. Do you wish to notify the sender?';
$labels['receiptread'] = 'Return Receipt (read)';
$labels['yourmessage'] = 'This is a Return Receipt for your message';
$labels['receiptnote'] = 'Note: This receipt only acknowledges that the message was displayed on the recipient\'s computer. There is no guarantee that the recipient has read or understood the message contents.';

// address boook
$labels['name']         = 'Monstrar nomine';
$labels['firstname']    = 'Nomine';
$labels['surname']      = 'Nomine de familia';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefixo';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Pseudonymo';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Departimento';
$labels['gender']       = 'Sexo:';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'E-posta';
$labels['phone']        = 'Telephono';
$labels['address']      = 'Adresse';
$labels['street']       = 'Strata';
$labels['locality']     = 'Citate';
$labels['zipcode']      = 'Codice postal';
$labels['region']       = 'Provincia o stato';
$labels['country']      = 'Pais';
$labels['birthday']     = 'Anniversario de nativitate';
$labels['anniversary']  = 'Anniversario';
$labels['website']      = 'Sito web';
$labels['instantmessenger'] = 'MI';
$labels['notes'] = 'Notas';
$labels['male']   = 'masculin';
$labels['female'] = 'feminin';
$labels['manager'] = 'Gerente';
$labels['assistant'] = 'Assistente';
$labels['spouse'] = 'Sposo/a';
$labels['allfields'] = 'Omne campos';
$labels['search'] = 'Cercar';
$labels['advsearch'] = 'Cerca avantiate';
$labels['advanced'] = 'Avantiate';
$labels['other'] = 'Altere';

$labels['typehome']   = 'Domo';
$labels['typework']   = 'Labor';
$labels['typeother']  = 'Altere';
$labels['typemobile']  = 'Mobile';
$labels['typemain']  = 'Principal';
$labels['typehomefax']  = 'Fax de domo';
$labels['typeworkfax']  = 'Fax de labor';
$labels['typecar']  = 'Car';
$labels['typepager']  = 'Pager';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Assistente';
$labels['typehomepage']  = 'Pagina principal';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profilo';

$labels['addfield'] = 'Adder campo...';
$labels['addcontact'] = 'Adder nove contacto';
$labels['editcontact'] = 'Modificar contacto';
$labels['contacts'] = 'Contactos';
$labels['contactproperties'] = 'Proprietates de contacto';
$labels['personalinfo'] = 'Information personal';

$labels['edit']   = 'Modificar';
$labels['cancel'] = 'Cancellar';
$labels['save']   = 'Salveguardar';
$labels['delete'] = 'Deler';
$labels['rename'] = 'Renominar';
$labels['addphoto'] = 'Adder';
$labels['replacephoto'] = 'Reimplaciar';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'Crear un nove carta de contacto';
$labels['deletecontact']  = 'Deler contactos seligite';
$labels['composeto']      = 'Compose mail to';
$labels['contactsfromto'] = 'Contacts $from to $to of $count';
$labels['print']          = 'Imprimer';
$labels['export']         = 'Exportar';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Crear un nove gruppo de contacto';
$labels['grouprename']    = 'Renominar gruppo';
$labels['groupdelete']    = 'Deler gruppo';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Show previous page';
$labels['firstpage']      = 'Show first page';
$labels['nextpage']       = 'Show next page';
$labels['lastpage']       = 'Show last page';

$labels['group'] = 'Gruppo';
$labels['groups'] = 'Gruppos';
$labels['personaladrbook'] = 'Adresses personal';

$labels['searchsave'] = 'Salveguardar cerca';
$labels['searchdelete'] = 'Deler cerca';

$labels['import'] = 'Importar';
$labels['importcontacts'] = 'Importar contactos';
$labels['importfromfile'] = 'Importar ex file:';
$labels['importtarget'] = 'Adder nove contactos al adressario';
$labels['importreplace'] = 'Replace the entire address book';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'Facite';

// settings
$labels['settingsfor'] = 'Configurationes pro';
$labels['about'] = 'A proposito';
$labels['preferences'] = 'Preferentias';
$labels['userpreferences'] = 'Preferentias de usator';
$labels['editpreferences'] = 'Modificar preferentias de usator';

$labels['identities'] = 'Identitates';
$labels['manageidentities'] = 'Manage identities for this account';
$labels['newidentity'] = 'Nove identitate';

$labels['newitem'] = 'Nove elemento';
$labels['edititem'] = 'Modificar elemento';

$labels['preferhtml'] = 'Monstrar HTML';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'Message HTML';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Formato de data';
$labels['timeformat'] = 'Formato de tempore';
$labels['prettydate'] = 'Pretty dates';
$labels['setdefault']  = 'Fixar predeterminate';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'Lingua';
$labels['timezone']  = 'Fuso horari';
$labels['pagesize']  = 'Rows per page';
$labels['signature'] = 'Signatura';
$labels['dstactive']  = 'Daylight saving time';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Compose HTML messages';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'Signatura HTML';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'Show preview pane';
$labels['skin'] = 'Apparentia de interfacie';
$labels['logoutclear'] = 'Clear Trash on logout';
$labels['logoutcompact'] = 'Compact Inbox on logout';
$labels['uisettings'] = 'Interfacie de usator';
$labels['serversettings'] = 'Configurationes de servitor';
$labels['mailboxview'] = 'Mailbox View';
$labels['mdnrequests'] = 'On request for return receipt';
$labels['askuser'] = 'ask me';
$labels['autosend'] = 'send receipt';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'ignorar';
$labels['readwhendeleted'] = 'Mark the message as read on delete';
$labels['flagfordeletion'] = 'Flag the message for deletion instead of delete';
$labels['skipdeleted'] = 'Do not show deleted messages';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'from known senders';
$labels['always'] = 'sempre';
$labels['showinlineimages'] = 'Display attached images below the message';
$labels['autosavedraft']  = 'Automatically save draft';
$labels['everynminutes']  = 'cata $n minuta(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'nunquam';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'Inviar me_ssages';
$labels['messagescomposition'] = 'Composing Messages';
$labels['mimeparamfolding'] = 'Nomines de attachamento';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (other)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'Optiones avantiate';
$labels['focusonnewmessage'] = 'Focus browser window on new message';
$labels['checkallfolders'] = 'Check all folders for new messages';
$labels['displaynext'] = 'After message delete/move display the next message';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'Optiones principal';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'Section';
$labels['maintenance'] = 'Mantenimento';
$labels['newmessage'] = 'Nove message';
$labels['signatureoptions'] = 'Optiones de signatura';
$labels['whenreplying'] = 'When replying';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'start new message above the quote';
$labels['replybottomposting'] = 'start new message below the quote';
$labels['replyremovesignature'] = 'When replying remove original signature from message';
$labels['autoaddsignature'] = 'Automaticamente inserta signatura';
$labels['newmessageonly'] = 'new message only';
$labels['replyandforwardonly'] = 'replies and forwards only';
$labels['insertsignature'] = 'Insertar signatura';
$labels['previewpanemarkread']  = 'Mark previewed messages as read';
$labels['afternseconds']  = 'after $n seconds';
$labels['reqmdn'] = 'Always request a return receipt';
$labels['reqdsn'] = 'Always request a delivery status notification';
$labels['replysamefolder'] = 'Place replies in the folder of the message being replied to';
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'Skip alternative email addresses in autocompletion';
$labels['listnamedisplay'] = 'List contacts as';
$labels['spellcheckbeforesend'] = 'Check spelling before sending a message';
$labels['spellcheckoptions'] = 'Optiones de verification orthographic';
$labels['spellcheckignoresyms'] = 'Ignore words with symbols';
$labels['spellcheckignorenums'] = 'Ignore words with numbers';
$labels['spellcheckignorecaps'] = 'Ignore words with all letters capitalized';
$labels['addtodict'] = 'Adder al dictionario';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'Dossier';
$labels['folders']  = 'Dossieres';
$labels['foldername']  = 'Nomine de dossier';
$labels['subscribed']  = 'Subscribite';
$labels['messagecount'] = 'Messages';
$labels['create']  = 'Crear';
$labels['createfolder']  = 'Crear un nove dossier';
$labels['managefolders']  = 'Gerer le dossieres';
$labels['specialfolders'] = 'Special Folders';
$labels['properties'] = 'Proprietates';
$labels['folderproperties'] = 'Proprietates de dossier';
$labels['parentfolder'] = 'Parent folder';
$labels['location'] = 'Location';
$labels['info'] = 'Information';
$labels['getfoldersize'] = 'Click to get folder size';
$labels['changesubscription'] = 'Click to change subscription';
$labels['foldertype'] = 'Typo de dossier';
$labels['personalfolder']  = 'Dossier private';
$labels['otherfolder']  = 'Other User\'s Folder';
$labels['sharedfolder']  = 'Dossier public';

$labels['sortby'] = 'Ordinar per';
$labels['sortasc']  = 'Sort ascending';
$labels['sortdesc'] = 'Sort descending';
$labels['undo'] = 'Undo';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Fonte';
$labels['license'] = 'Licentia';
$labels['support'] = 'Obtener supporte';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'Anglese';
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
$labels['japanese'] = 'Japonese';
$labels['korean'] = 'Coreano';
$labels['chinese'] = 'Chinese';

?>

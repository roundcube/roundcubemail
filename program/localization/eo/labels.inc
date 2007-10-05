<?php

/*

 +-----------------------------------------------------------------------+
 | language/eo/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Andreas van Cranenburgh <andreas@unstable.nl>                 |
 +-----------------------------------------------------------------------+

 @version $Id: labels.inc 858 2007-10-04 10:14:19Z Stiwi $

*/


$labels = array();

// login page
$labels['welcome']   = 'Bonvenon al $product';
$labels['username']  = 'Uzanta-nomo';
$labels['password']  = 'Pasvorto';
$labels['server']    = 'Servilo';
$labels['login']     = 'Ensalutu';

// taskbar
$labels['logout']   = 'Elsalutu';
$labels['mail']     = 'Retpoŝto';
$labels['settings'] = 'Preferoj';
$labels['addressbook'] = 'Adresaro';

// mailbox names
$labels['inbox']  = 'Ricevitujo';
$labels['drafts'] = 'Malnetujo';
$labels['sent']   = 'Senditujo';
$labels['trash']  = 'Rubujo';
$labels['junk']   = 'Spamujo'; #FIXME. eble fatrasujo.

// message listing
$labels['subject'] = 'Subjekto'; #temo?
$labels['from']    = 'Sendanto';
$labels['to']      = 'Ricevanto';
$labels['cc']      = 'Kopio';
$labels['bcc']     = 'Blindkopio';
$labels['replyto'] = 'Respondu-Al';
$labels['date']    = 'Dato';
$labels['size']    = 'Grando';
$labels['priority'] = 'Prioritato';
$labels['organization'] = 'Organizo';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Dosierujoj';
$labels['messagesfromto'] = 'Mesaĝoj ekde $from ĝis $to de $count';
$labels['messagenrof'] = 'Mesaĝo $nr-a de $count';

$labels['moveto']   = 'transloku al...';
$labels['download'] = 'elŝutu';

$labels['filename'] = 'Dosiernomo';
$labels['filesize'] = 'Dosiergrando';

$labels['preferhtml'] = 'Montru HTML';
$labels['htmlmessage'] = 'HTML Mesaĝo';
$labels['prettydate'] = 'Agrablaj Datoj';

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
$labels['monday']    = 'Luno';
$labels['tuesday']   = 'Mardo';
$labels['wednesday'] = 'Merkredo';
$labels['thursday']  = 'Ĵaŭdo';
$labels['friday']    = 'Vendredo';
$labels['saturday']  = 'Sabato';

$labels['today'] = 'Hodiaŭ';

// toolbar buttons
$labels['checkmail']        = 'Kontrolu por novaj mesaĝoj';
$labels['writenewmessage']  = 'Verku novan mesaĝon';
$labels['replytomessage']   = 'Respondu al mesaĝo';
$labels['replytoallmessage'] = 'Respondu al sendanto kaj ĉiu ricevanto';
$labels['forwardmessage']   = 'Plusendu mesaĝon';
$labels['deletemessage']    = 'Transloku mesaĝon al rubujo';
$labels['printmessage']     = 'Printu ĉi tiun mesaĝon';
$labels['previousmessage']  = 'Montru antaŭan mesaĝon';
$labels['previousmessages'] = 'Montru antaŭan aron de mesaĝoj';
$labels['firstmessage']     = 'Montru unuan mesaĝon';
$labels['firstmessages']    = 'Montru unuan aron de mesaĝoj';
$labels['nextmessage']      = 'Montru sekvan mesaĝon';
$labels['nextmessages']     = 'Montru sekvan aron de mesaĝoj';
$labels['lastmessage']      = 'Montru lastan mesaĝon';
$labels['lastmessages']     = 'Montru lastan aron de mesaĝoj';
$labels['backtolist']       = 'Reen al mesaĝo-listo';
$labels['viewsource']       = 'Montru fonton';

$labels['select'] = 'Elektu';
$labels['all'] = 'Ĉion';
$labels['none'] = 'Nenion';
$labels['unread'] = 'Nelegatan';

$labels['compact'] = 'Kompakta';
$labels['empty'] = 'Malplena';
$labels['purge'] = 'Senrubigu';

$labels['quota'] = 'Diskuzo';
$labels['unknown']  = 'nekonata';
$labels['unlimited']  = 'senlimo';

$labels['quicksearch']  = 'Rapida serĉo';
$labels['resetsearch']  = 'Vakigu serĉon';


// message compose
$labels['compose']        = 'Verku mesaĝon';
$labels['sendmessage']    = 'Sendu mesaĝon nun';
$labels['savemessage']  = 'Savu ĉi tiun malneton';
$labels['addattachment']  = 'Alfiksu dosieron';
$labels['charset']        = 'Karakteraro';
$labels['editortype']     = 'Speco de redaktilo'; #???
$labels['returnreceipt']  = 'Return receipt';

$labels['checkspelling'] = 'Kontrolu grafismon';
$labels['resumeediting'] = 'Rekomencu redakti';
$labels['revertto']      = 'Reiru al';

$labels['attachments'] = 'Alfiksaĵoj';
$labels['upload'] = 'Alŝutu';
$labels['close']  = 'Fermu';

$labels['low']     = 'Malalta';
$labels['lowest']  = 'Plej malalta';
$labels['normal']  = 'Normala';
$labels['high']    = 'Alta';
$labels['highest'] = 'Plej alta';

$labels['nosubject']  = '(sensubjekto)';
$labels['showimages'] = 'Montru bildojn';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Plata teksto';


// address boook
$labels['name']      = 'Nomo';
$labels['firstname'] = 'Antaŭa nomo';
$labels['surname']   = 'Familia nomo';
$labels['email']     = 'Retpoŝto';

$labels['addcontact'] = 'Aldonu kontakton';
$labels['editcontact'] = 'Redaktu kontakton';

$labels['edit']   = 'Redaktu';
$labels['cancel'] = 'Rezignu';
$labels['save']   = 'Savu';
$labels['delete'] = 'Forigu';

$labels['newcontact']     = 'Kreu novan kontaktaĵon';
$labels['deletecontact']  = 'Forigu elektatajn kontaktojn';
$labels['composeto']      = 'Verku mesaĝon al';
$labels['contactsfromto'] = 'Kontaktoj ekde $from ĝis $to de $count';
$labels['print']          = 'Printu';
$labels['export']         = 'Eksportu';

$labels['previouspage']   = 'Montru auntaŭan aron';
$labels['firstpage']      = 'Montru unuan aron';
$labels['nextpage']       = 'Montru sekvan aron';
$labels['lastpage']       = 'Montru lastan aron';

$labels['groups'] = 'Grupoj';
$labels['personaladrbook'] = 'Persona Adresoj';


// settings
$labels['settingsfor']  = 'Preferoj de';

$labels['preferences']  = 'Preferoj';
$labels['userpreferences']  = 'Uzant-preferoj';
$labels['editpreferences']  = 'Redajtu uzant-preferojn';

$labels['identities']  = 'Identoj';
$labels['manageidentities']  = 'Administru identoj de ĉi tiu konto';
$labels['newidentity']  = 'Nova idento';

$labels['newitem']  = 'Nova ero';
$labels['edititem']  = 'Redaktu eron';

$labels['setdefault']  = 'Agordu kiel defaŭlta';
$labels['language']  = 'Lingvo';
$labels['timezone']  = 'Tempzono';
$labels['pagesize']  = 'Mesaĝoj po paĝo';
$labels['signature'] = 'Subskribo';
$labels['dstactive']  = 'Taglumsavado';
$labels['htmleditor'] = 'Verku HTML mesaĝojn';
$labels['htmlsignature'] = 'HTML subskribo';
$labels['previewpane'] = 'Montru antaŭrigardejon';

$labels['autosavedraft']  = 'Aŭtomate savu malneton';
$labels['everynminutes']  = 'ĉiu $n minutoj';
$labels['never']  = 'neniam';

$labels['folder']  = 'Dosierujo';
$labels['folders']  = 'Dosierujoj';
$labels['foldername']  = 'Dosierujo-nomo';
$labels['subscribed']  = 'Abonata';
$labels['create']  = 'Kreu';
$labels['createfolder']  = 'Kreu novan dosierujon';
$labels['rename'] = 'Renomu';
$labels['renamefolder'] = 'Renomu dosierujon';
$labels['deletefolder']  = 'Forigu dosierujon';
$labels['managefolders']  = 'Administru dosierujon';

$labels['sortby'] = 'Ordigu per';
$labels['sortasc']  = 'Ordigu kreskante';
$labels['sortdesc'] = 'Ordigu malkreskante';

?>
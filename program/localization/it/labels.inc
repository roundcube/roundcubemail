<?php

/*
 +-----------------------------------------------------------------------+
 | language/it/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Paolo Asperti <paolo@asperti.com>                              |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Utente';
$labels['password']  = 'Password';
$labels['server']    = 'Server';
$labels['login']     = 'Entra';

// taskbar
$labels['logout']   = 'Esci';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Impostazioni';
$labels['addressbook'] = 'Rubrica';

// mailbox names
$labels['inbox']  = 'Posta in arrivo';
$labels['sent']   = 'Inviata';
$labels['trash']  = 'Cestino';
$labels['drafts'] = 'Bozze';
$labels['junk']   = 'Spam';

// message listing
$labels['subject'] = 'Oggetto';
$labels['from']    = 'Mittente';
$labels['to']      = 'Destinatario';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Ccn';
$labels['replyto'] = 'Rispondi a';
$labels['date']    = 'Data';
$labels['size']    = 'Dimensione';
$labels['priority'] = 'Priorità';
$labels['organization'] = 'Società';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Cartelle';
$labels['messagesfromto'] = 'Messaggi da $from a $to di $count';
$labels['messagenrof'] = 'Messaggio $nr di $count';

$labels['moveto']   = 'sposta...';
$labels['download'] = 'download';

$labels['filename'] = 'Nome file';
$labels['filesize'] = 'Dimensione file';

$labels['preferhtml'] = 'Preferisci HTML';
$labels['htmlmessage'] = 'Messaggio HTML';
$labels['prettydate'] = 'Date più leggibili';

$labels['addtoaddressbook'] = 'Aggiungi alla rubrica';

// weekdays short
$labels['sun'] = 'Dom';
$labels['mon'] = 'Lun';
$labels['tue'] = 'Mar';
$labels['wed'] = 'Mer';
$labels['thu'] = 'Gio';
$labels['fri'] = 'Ven';
$labels['sat'] = 'Sab';

// weekdays long
$labels['sunday']    = 'Domenica';
$labels['monday']    = 'Lunedì';
$labels['tuesday']   = 'Martedì';
$labels['wednesday'] = 'Mercoledì';
$labels['thursday']  = 'Giovedì';
$labels['friday']    = 'Venerdì';
$labels['saturday']  = 'Sabato';

$labels['today'] = 'Oggi';

// toolbar buttons
$labels['writenewmessage']  = 'Scrivi un nuovo messaggio';
$labels['replytomessage']   = 'Rispondi al messaggio';
$labels['replytoallmessage'] = 'Rispondi a tutti';
$labels['forwardmessage']   = 'Inoltra il messaggio';
$labels['deletemessage']    = 'Sposta il messaggio nel cestino';
$labels['printmessage']     = 'Stampa il messaggio';
$labels['previousmessages'] = 'Visualizza messaggi precedenti';
$labels['nextmessages']     = 'Visualizza messaggi successivi';
$labels['backtolist']       = 'Torna alla lista messaggi';
$labels['viewsource']       = 'Visualizza sorgente messaggio';

$labels['select'] = 'Seleziona';
$labels['all'] = 'Tutti';
$labels['none'] = 'Nessuno';
$labels['unread'] = 'Non letti';

$labels['compact'] = 'Compatta';
$labels['empty'] = 'Svuota';
$labels['purge'] = 'Pulisci';

$labels['quota'] = 'Utilizzo spazio';


// message compose
$labels['compose']  = 'Componi un messaggio';
$labels['sendmessage']  = 'Invia il messaggio adesso';
$labels['addattachment']  = 'Allega un file';
$labels['charset']  = 'Set di caratteri';

$labels['attachments'] = 'Allegati';
$labels['upload'] = 'Aggiungi';
$labels['close']  = 'Chiudi';

$labels['low']     = 'Bassa';
$labels['lowest']  = 'Molto bassa';
$labels['normal']  = 'Normale';
$labels['high']    = 'Alta';
$labels['highest'] = 'Molto alta';

$labels['nosubject']  = '(nessun oggetto)';
$labels['showimages'] = 'Visualizza immagini';


// address boook
$labels['name']      = 'Nome visualizzato';
$labels['firstname'] = 'Nome';
$labels['surname']   = 'Cognome';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Aggiungi un nuovo contatto';
$labels['editcontact'] = 'Modifica contatto';

$labels['edit']   = 'Modifica';
$labels['cancel'] = 'Annulla';
$labels['save']   = 'Salva';
$labels['delete'] = 'Elimina';

$labels['newcontact']     = 'Crea un nuovo contatto';
$labels['addcontact']     = 'Aggiungi il contatto selezionato alla rubrica';
$labels['deletecontact']  = 'Elimina i contatti selezionati';
$labels['composeto']      = 'Invia email a';
$labels['contactsfromto'] = 'Contatti da $from a $to di $count';
$labels['print']          = 'Stampa';
$labels['export']         = 'Esporta';

// LDAP search
$labels['ldapsearch'] = 'Cerca su directory LDAP';

$labels['ldappublicsearchname']    = 'Nome contatto';
$labels['ldappublicsearchtype'] = 'Nome esatto';
$labels['ldappublicserverselect'] = 'Scegli server';
$labels['ldappublicsearchfield'] = 'Cerca per';
$labels['ldappublicsearchform'] = 'Cerca un contatto';
$labels['ldappublicsearch'] = 'Cerca';


// settings
$labels['settingsfor']  = 'Impostazioni per ';

$labels['preferences']  = 'Preferenze';
$labels['userpreferences']  = 'Preferenze utente';
$labels['editpreferences']  = 'Modifica le preferenze per l\'utente';

$labels['identities']  = 'Identità';
$labels['manageidentities']  = 'Gestisci le identità per questo account';
$labels['newidentity']  = 'Nuova identità';

$labels['newitem']  = 'Nuovo elemento';
$labels['edititem']  = 'Modifica elemento';

$labels['setdefault']  = 'Imposta predefinita';
$labels['language']  = 'Lingua';
$labels['timezone']  = 'Fuso orario';
$labels['pagesize']  = 'Righe per pagina';
$labels['signature'] = 'Firma';

$labels['folder']  = 'Cartella';
$labels['folders']  = 'Cartelle';
$labels['foldername']  = 'Nome cartella';
$labels['subscribed']  = 'Sottoscritta';
$labels['create']  = 'Crea';
$labels['createfolder']  = 'Crea una nuova cartella';
$labels['deletefolder']  = 'Cancella la cartella';
$labels['managefolders']  = 'Gestione cartelle';

$labels['sortby'] = 'Ordina per';
$labels['sortasc']  = 'Ordinamento ascendente';
$labels['sortdesc'] = 'Ordinamento discendente';


?>
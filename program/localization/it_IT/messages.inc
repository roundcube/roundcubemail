<?php

/*

+-----------------------------------------------------------------------+
| language/it_IT/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Andrej Mocilnik <amocilnik@dotcom.ts.it>                      |
|         Yusef Maali <contact@yusefmaali.net>                          |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Impossibile accedere. Utente o password non corretti';
$messages['cookiesdisabled'] = 'Il tuo browser non accetta i cookies';
$messages['sessionerror'] = 'Sessione non valida o scaduta';
$messages['imaperror'] = 'Impossibile connettersi al server IMAP';
$messages['servererror'] = 'Errore del server!';
$messages['servererrormsg'] = 'Errore del server: $msg';
$messages['errorreadonly'] = 'Impossibile eseguire l\'operazione. Cartella in sola lettura';
$messages['errornoperm'] = 'Impossibile eseguire l\'operazione. Permesso negato';
$messages['invalidrequest'] = 'Richiesta non valida! Nessun dato salvato.';
$messages['nomessagesfound'] = 'Nessun messaggio trovato in questa cartella';
$messages['loggedout'] = 'Sessione chiusa correttamente. Arrivederci!';
$messages['mailboxempty'] = 'La casella è vuota';
$messages['loading'] = 'Caricamento...';
$messages['uploading'] = 'Caricamento file...';
$messages['loadingdata'] = 'Caricamento dati...';
$messages['checkingmail'] = 'Controllo nuovi messaggi...';
$messages['sendingmessage'] = 'Invio messaggio in corso...';
$messages['messagesent'] = 'Messaggio inviato correttamente';
$messages['savingmessage'] = 'Salvataggio messaggio...';
$messages['messagesaved'] = 'Messaggio salvato in bozze';
$messages['successfullysaved'] = 'Salvato correttamente';
$messages['addedsuccessfully'] = 'Contatto aggiunto alla rubrica';
$messages['contactexists'] = 'Esiste già un contatto con questo indirizzo e-mail';
$messages['blockedimages'] = 'Per proteggere la tua privacy, le immagini remote di questo messaggio sono state bloccate.';
$messages['encryptedmessage'] = 'Questo messaggio é cifrato e non può essere visualizzato. Spiacenti!';
$messages['nocontactsfound'] = 'Nessun contatto trovato';
$messages['contactnotfound'] = 'Il contatto richiesto non è stato trovato';
$messages['sendingfailed'] = 'Impossibile inviare il messaggio';
$messages['senttooquickly'] = 'Per favore, attendi $sec secondi prima di inviare questo messaggio';
$messages['errorsavingsent'] = 'C\'è stato un errore nel salvare il messaggio inviato';
$messages['errorsaving'] = 'Impossibile salvare';
$messages['errormoving'] = 'Impossibile spostare il messaggio';
$messages['errorcopying'] = 'Impossibile copiare il messaggio';
$messages['errordeleting'] = 'Impossibile eliminare il messaggio';
$messages['errormarking'] = 'Impossibile segnare il messaggio';
$messages['deletecontactconfirm'] = 'Sei sicuro di voler eliminare i contatti selezionati?';
$messages['deletemessagesconfirm'] = 'Sei sicuro di voler eliminare i messaggi selezionati?';
$messages['deletefolderconfirm'] = 'Sei sicuro di voler eliminare la cartella selezionata?';
$messages['purgefolderconfirm'] = 'Sei sicuro di voler eliminare tutti i messaggi in questa cartella?';
$messages['folderdeleting'] = 'Eliminazione cartella...';
$messages['foldermoving'] = 'Spostamento cartella...';
$messages['foldersubscribing'] = 'Sottoscrizione cartella...';
$messages['folderunsubscribing'] = 'Cancellazione sottoscrizzione cartella...';
$messages['formincomplete'] = 'Per favore, compila tutti i campi';
$messages['noemailwarning'] = 'Per favore, immetti un indirizzo e-mail valido';
$messages['nonamewarning'] = 'Per favore, immetti un nome';
$messages['nopagesizewarning'] = 'Per favore, immetti il numero di righe per pagina';
$messages['nosenderwarning'] = 'Per favore, inserisci l\'indirizzo e-mail del mittente';
$messages['norecipientwarning'] = 'Per favore, immetti almeno un destinatario';
$messages['nosubjectwarning'] = 'L\'oggetto è vuoto. Vuoi inserirlo adesso?';
$messages['nobodywarning'] = 'Inviare il messaggio senza testo?';
$messages['notsentwarning'] = 'Il messaggio non è stato inviato. Vuoi annullare questo messaggio?';
$messages['noldapserver'] = 'Per favore, scegli un server LDAP in cui ricercare';
$messages['nocontactsreturned'] = 'Nessun contatto trovato';
$messages['nosearchname'] = 'Per favore, immetti un nome o un indirizzo e-mail';
$messages['notuploadedwarning'] = 'Non tutti gli allegati sono stati ancora caricati. Prego attendere, oppure cancellare il caricamento.';
$messages['searchsuccessful'] = '$nr messaggi trovati';
$messages['searchnomatch'] = 'La ricerca non ha dato nessun risultato';
$messages['searching'] = 'Ricerca...';
$messages['checking'] = 'Controllo...';
$messages['nospellerrors'] = 'Nessun errore ortografico trovato';
$messages['folderdeleted'] = 'Cartella eliminata';
$messages['foldersubscribed'] = 'Cartella sottoscritta correttamente';
$messages['folderunsubscribed'] = 'Sottoscrizione cartella cancellata correttamente';
$messages['folderpurged'] = 'Cartella svuotata correttamente';
$messages['folderexpunged'] = 'Cartella compattata correttamente';
$messages['deletedsuccessfully'] = 'Eliminato con successo';
$messages['converting'] = 'Rimozione della formattazione dal messaggio...';
$messages['messageopenerror'] = 'Impossibile caricare il messaggio dal server';
$messages['fileuploaderror'] = 'Errore durante il caricamento del file';
$messages['filesizeerror'] = 'Il file da caricare supera il limite massimo di $size';
$messages['copysuccess'] = 'Copiati $nr indirizzi';
$messages['copyerror'] = 'Impossibile copiare gli indirizzi';
$messages['sourceisreadonly'] = 'La rubrica è in sola lettura';
$messages['errorsavingcontact'] = 'Impossibile salvare il contatto';
$messages['movingmessage'] = 'Spostamento del messaggio...';
$messages['copyingmessage'] = 'Copia del messaggio';
$messages['deletingmessage'] = 'Cancellazione messaggi/o';
$messages['markingmessage'] = 'Marca messaggi/o...';
$messages['receiptsent'] = 'Ricevuta di ritorno inviata con successo';
$messages['errorsendingreceipt'] = 'Impossibile spedire la ricevuta di ritorno';
$messages['nodeletelastidentity'] = 'Impossibile eliminare l\'unica identità disponibile';
$messages['forbiddencharacter'] = 'Il nome della cartella contiene un carattere vietato';
$messages['selectimportfile'] = 'Per favore, seleziona il file da caricare';
$messages['addresswriterror'] = 'La rubrica selezionata non è scrivibile';
$messages['contactaddedtogroup'] = 'Contatto aggiunto con successo al gruppo';
$messages['contactremovedfromgroup'] = 'Contatto rimosso con successo dal gruppo';
$messages['importwait'] = 'Importazione in corso, attendere...';
$messages['importerror'] = 'Importazione fallita! Il file caricato non è un file vCard valido.';
$messages['importconfirm'] = '<b>Importati con successo $inserted contatti, saltate $skipped voci esistenti</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operazione non consentita!';
$messages['nofromaddress'] = 'Indirizzo e-mail mancante nell\'identità selezionata';
$messages['editorwarning'] = 'Passare all\'editor testuale farà perdere tutte le informazioni di formattazione. Sicuro di voler continuare?';
$messages['httpreceivedencrypterror'] = 'Fatale errore di configurazione. Per favore contatta l\'amministratore immediatamente. Il tuo messaggio non può essere inviato.';
$messages['smtpconnerror'] = 'Errore SMTP ($code): Connessione al server fallita';
$messages['smtpautherror'] = 'Errore SMTP ($code): Autenticazione fallita';
$messages['smtpfromerror'] = 'Errore SMTP ($code): Fallita l\'impostazione del mittente "$from" ($msg)';
$messages['smtptoerror'] = 'Errore SMTP ($code): Fallito l\'inserimento del destinatario "$to" ($msg)';
$messages['smtprecipientserror'] = 'Errore SMTP: Impossibile processare la lista dei destinatari';
$messages['smtpdsnerror'] = 'Errore SMTP: Ricevuta di consegna non supportata';
$messages['smtperror'] = 'Errore SMTP: $msg';
$messages['emailformaterror'] = 'Indirizzo e-mail non corretto: $email';
$messages['toomanyrecipients'] = 'Numero eccessivo di destinatari, ridurlo a $max';
$messages['maxgroupmembersreached'] = 'Il numero dei membri del gruppo eccede il massimo di $max';
$messages['internalerror'] = 'Si è verificato un errore interno. Si prega di riprovare di nuovo.';
$messages['contactdelerror'] = 'Impossibile eliminare contatto/i';
$messages['contactdeleted'] = 'Contatto/i correttamente eliminato';
$messages['groupdeleted'] = 'Gruppo correttamente eliminato';
$messages['grouprenamed'] = 'Gruppo correttamente rinominato';
$messages['groupcreated'] = 'Gruppo creato correttamente';
$messages['messagedeleted'] = 'Messaggi/o cancellato correttamente';
$messages['messagemoved'] = 'Messaggi/o spostato correttamente';
$messages['messagecopied'] = 'Messaggi/o copiato correttamente';
$messages['messagemarked'] = 'Messaggi/o marcato correttamente';
$messages['autocompletechars'] = 'Inserisci almeno $min caratteri per l\'autocompletamento';
$messages['namecannotbeempty'] = 'Il nome non può essere vuoto';
$messages['nametoolong'] = 'Nome troppo lungo';
$messages['folderupdated'] = 'Cartella aggiornata correttamente';
$messages['foldercreated'] = 'Cartella creata correttamente';
$messages['invalidimageformat'] = 'Formato non valido';

?>

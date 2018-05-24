<?php

/**
 +-----------------------------------------------------------------------+
 | plugins/enigma/localization/<lang>.inc                                |
 |                                                                       |
 | Localization file of the Roundcube Webmail ACL plugin                 |
 | Copyright (C) 2012-2016, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/roundcube/roundcube-webmail/plugin-enigma/
*/
$labels['encryption'] = 'Criptare';
$labels['enigmacerts'] = 'Certificati S/MIME';
$labels['enigmakeys'] = 'Chiavi PGP';
$labels['keysfromto'] = 'Chiavi da $from a $to di $count';
$labels['keyname'] = 'Nome';
$labels['keyid'] = 'ID della chiave';
$labels['keyuserid'] = 'ID utente';
$labels['keytype'] = 'Tipo chiave';
$labels['fingerprint'] = 'Fingerprint';
$labels['subkeys'] = 'Sottochiavi';
$labels['keyprops'] = 'Proprietà chiave';
$labels['basicinfo'] = 'Informazioni base';
$labels['userids'] = 'Utenti aggiuntivi';
$labels['typepublickey'] = 'chiave pubblica';
$labels['typekeypair'] = 'coppia di chiavi';
$labels['keyattfound'] = 'Questo messaggio contiene chiavi PGP in allegato.';
$labels['keyattimport'] = 'Importa chiave(i)';
$labels['typesign'] = 'Firma';
$labels['typeencrypt'] = 'Cifra';
$labels['typecert'] = 'Certifica';
$labels['typeauth'] = 'Autenticazione';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algoritmo';
$labels['subkeycreated'] = 'Creazione';
$labels['subkeyexpires'] = 'Scadenza';
$labels['subkeyusage'] = 'Utilizzo';
$labels['expiresnever'] = 'mai';
$labels['unknown'] = 'sconosciuto';
$labels['uservalid'] = 'Valido';
$labels['userid'] = 'ID';
$labels['valid'] = 'valido';
$labels['supportencryption'] = 'Abilita cifratura firma del messaggio';
$labels['supportsignatures'] = 'Abilita la verifica delle firme dei messaggi';
$labels['supportdecryption'] = 'Abilita la decifrazione del messaggio';
$labels['signdefault'] = 'Firma tutti i messaggi in modo predefinito';
$labels['encryptdefault'] = 'Cifra tutti i messaggi in modo predefinito';
$labels['attachpubkeydefault'] = 'Allega la mia chiave pubblica PGP in modo predefinito';
$labels['passwordtime'] = 'Mantiene le password delle chiavi private per ';
$labels['nminutes'] = '$m minuti';
$labels['wholesession'] = 'l\'intera sessione';
$labels['createkeys'] = 'Crea una nuova coppia di chiavi';
$labels['importkeys'] = 'Importa chiave(i)';
$labels['exportkeys'] = 'Esporta chiave(i)';
$labels['keyactions'] = 'Azione chiave...';
$labels['keyremove'] = 'Rimuovi';
$labels['keydisable'] = 'Disabilita';
$labels['keyrevoke'] = 'Revoca';
$labels['keysend'] = 'Invia una chiave pubblica in un messaggio';
$labels['keychpass'] = 'Cambia password';
$labels['newkeyident'] = 'Identità';
$labels['newkeypass'] = 'Password';
$labels['newkeypassconfirm'] = 'Conferma password';
$labels['newkeysize'] = 'Dimensione chiave';
$labels['key2048'] = '2048 bit - predefinito';
$labels['key4096'] = '4096 bit - più sicuro';
$labels['keygenerating'] = 'Generazione chiavi...';
$labels['encryptionoptions'] = 'Opzioni di cifratura...';
$labels['encryptmsg'] = 'Cifra questo messaggio';
$labels['signmsg'] = 'Firma digitalmente questo messaggio';
$labels['sendunencrypted'] = 'Invia non cifrato';
$labels['enterkeypasstitle'] = 'Inserisci frase sicura della chiave';
$labels['enterkeypass'] = 'È richiesta una frase segreta per sbloccare la chiave ($keyid) per l\'utente: $user.';
$labels['arialabelkeyexportoptions'] = 'Opzioni esportazione chiave';
$labels['attachpubkeymsg'] = 'Allega la mia chiave pubblica';
$labels['keyexportprompt'] = 'Vuoi includere le chiavi segrete nel file di chiavi OpenPGP salvato?';
$labels['onlypubkeys'] = 'Esporta solo chiavi pubbliche';
$labels['withprivkeys'] = 'Esporta chiavi segrete';
$labels['findkey'] = 'Cerca nei server';
$labels['keyimportlabel'] = 'Importa da file';
$labels['keyimportsearchlabel'] = 'Cerca nei server';
$labels['managekeys'] = 'Gestisci chiavi PGP';
$labels['identitymatchingprivkeys'] = 'Hai $nr chiavi private PGP corrispondenti memorizzate nel tuo portachiavi:';
$labels['identitynoprivkeys'] = 'L\'identità del mittente non ha ancora una chiave privata PGP memorizzata nel tuo portachiavi.';
$messages['sigvalid'] = 'Firma da $sender verificata.';
$messages['sigvalidpartial'] = 'Firma da $sender verificata, ma parte del corpo del messaggio non è firmata.';
$messages['siginvalid'] = 'Firma non valida da $sender.';
$messages['sigunverified'] = 'Firma non verificata. Certificato non verificato. ID certificato: $keyid.';
$messages['signokey'] = 'Firma non verificata. Chiave pubblica non trovata. ID chiave: $keyid.';
$messages['sigerror'] = 'Firma non verificata. Errore interno.';
$messages['decryptok'] = 'Messaggio decifrato.';
$messages['decrypterror'] = 'Decifrazione non riuscita.';
$messages['decryptnokey'] = 'Decifrazione non riuscita. Chiave privata non trovata. ID chiave: $keyid.';
$messages['decryptbadpass'] = 'Decifrazione non riuscita. Password non valida.';
$messages['decryptnopass'] = 'Decifrazione non riuscita. Password della chiave richiesta.';
$messages['decryptpartial'] = 'Messaggio decifrato, ma parte del corpo del messaggio non è firmata.';
$messages['signerror'] = 'Firma non riuscita.';
$messages['signnokey'] = 'Firma non riuscita. Chiave privata non trovata.';
$messages['signbadpass'] = 'Firma non riuscita. Password non valida.';
$messages['signnopass'] = 'Firma non riuscita. Password della chiave richiesta.';
$messages['encrypterror'] = 'Cifratura non riuscita.';
$messages['encryptnokey'] = 'Cifratura non riuscita. Chiave pubblica non trovata per $email.';
$messages['nokeysfound'] = 'Nessuna chiave trovata';
$messages['keynotfound'] = 'Chiave non trovata!';
$messages['keyopenerror'] = 'Impossibile ottenere informazioni sulla chiave. Errore interno.';
$messages['keylisterror'] = 'Impossibile elencare le chiavi. Errore interno.';
$messages['keysimportfailed'] = 'Impossibile importare la/le chiave/i. Errore interno.';
$messages['keysimportsuccess'] = 'Chiave/i importata/e correttamente. Importate: $new, non modificate: $old.';
$messages['keyremoving'] = 'Rimozione chiave/i...';
$messages['keyremoveconfirm'] = 'Sei sicuro di volere eliminare la/le chiave/i selezionata/e?';
$messages['keyremovesuccess'] = 'Chiave/i eliminata/e correttamente';
$messages['keyremoveerror'] = 'Impossibile eliminare la/le chiave/i selezionata/e.';
$messages['keyimporttext'] = 'Puoi importare chiavi private e pubbliche o firme di revoca in formato ASCII-Armor.';
$messages['keyimportsearchtext'] = 'Puoi cercare chiavi pubbliche in base all\'identificatore della chiave, per nome utente o indirizzo di posta elettronica e quindi importarle direttamente.';
$messages['formerror'] = 'Riempire il modulo. Tutti i campi sono richiesti.';
$messages['passwordsdiffer'] = 'Le password non corrispondono.';
$messages['keygenerateerror'] = 'Generazione di una coppia di chiavi non riuscita';
$messages['keygeneratesuccess'] = 'Una coppia di chiavi generata e importata correttamente.';
$messages['keygennosupport'] = 'Il browser non supporta la crittografia. Impossibile generare una coppia di chiavi.';
$messages['noidentselected'] = 'Devi selezionare almeno un\'identità per la chiave.';
$messages['nonameident'] = 'L\'identità deve avere un nome utente definito.';
?>

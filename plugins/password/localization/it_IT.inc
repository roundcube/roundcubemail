<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Password plugin            |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/

$labels['changepasswd']  = 'Modifica la Password';
$labels['curpasswd']  = 'Password corrente:';
$labels['newpasswd']  = 'Nuova password:';
$labels['confpasswd']  = 'Conferma la nuova Password:';
$messages['nopassword'] = 'Per favore inserire la nuova password.';
$messages['nocurpassword'] = 'Per favore inserire la password corrente.';
$messages['passwordincorrect'] = 'La password corrente non è corretta.';
$messages['passwordinconsistency'] = 'Le password non coincidono, per favore reinserire.';
$messages['crypterror'] = 'Impossibile salvare la nuova password. Funzione di crittografia mancante.';
$messages['connecterror'] = 'Impossibile salvare la nuova password. Errore di connessione.';
$messages['internalerror'] = 'Impossibile salvare la nuova password.';
$messages['passwordshort'] = 'La password deve essere lunga almeno $length caratteri.';
$messages['passwordweak'] = 'La password deve includere almeno una cifra decimale e un carattere di punteggiatura.';
$messages['passwordtooweak'] = 'La password è troppo debole.';
$messages['passwordnoseq'] = 'La password non deve essere una sequenza come 123456 o QWERTY.';
$messages['passwordnocommon'] = 'La password non deve contenere parole comuni o nomi.';
$messages['passwordforbidden'] = 'La password contiene caratteri proibiti.';
$messages['firstloginchange'] = 'Questo è il tuo primo accesso. Si prega di cambiare la propria password.';
$messages['disablednotice'] = 'Il sistema è attualmente in manutenzione e pertanto al momento non è possibile effettuare il cambio della password. Presto tutto tornerà alla normalità. Ci scusiamo per il disagio.';
$messages['passwinhistory'] = 'Questa password è già stata usata precedentemente';
$messages['samepasswd'] = 'La nuova password deve essere differente da una usata precedentemente.';
$messages['passwdexpirewarning'] = 'Attenzione! La tua password scadrà a breve, cambiala prima del $expiretiondatetime.';
$messages['passwdexpired'] = 'La tua password è scaduta, devi cambiarla adesso.';
$messages['passwdconstraintviolation'] = 'La password non soddisfa i requisiti di sicurezza.';
$messages['pwned_mustnotbedisclosed'] = 'La password non deve essere <a href="$href" target="_blank">nota</a>.';
$messages['pwned_isdisclosed'] = 'Questa password è nota.';
$messages['pwned_fetcherror'] = 'Verifica robustezza password fallita.';

$labels['hestia_err_policy_short'] = 'Password troppo corta (lunghezza minima: %min).';
$labels['hestia_err_endpoint_invalid'] = 'L\'endpoint Hestia non è configurato o non è valido.';
$labels['hestia_err_identity'] = 'Impossibile analizzare il nome utente in local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Impossibile determinare il proprietario del dominio per %domain.';
$labels['hestia_err_unknown'] = 'Errore sconosciuto da Hestia API.';
$labels['hestia_err_default'] = 'Hestia ha restituito il codice di errore %code.';
$labels['hestia_err_1'] = 'Non sono stati forniti argomenti sufficienti.';
$labels['hestia_err_2'] = 'Oggetto o argomento non valido.';
$labels['hestia_err_3'] = 'L\'oggetto non esiste (casella postale o dominio non trovati).';
$labels['hestia_err_4'] = 'L\'oggetto esiste già.';
$labels['hestia_err_5'] = 'L\'oggetto è già sospeso.';
$labels['hestia_err_6'] = 'L\'oggetto è già insospeso.';
$labels['hestia_err_7'] = 'Oggetto in uso; non può essere modificato.';
$labels['hestia_err_8'] = 'Funzionamento bloccato dai limiti del pacchetto.';
$labels['hestia_err_9'] = 'Password errata o non valida.';
$labels['hestia_err_10'] = 'Autorizzazione negata da Hestia (l\'identità API non ha diritti o l\'IP non è consentito).';
$labels['hestia_err_11'] = 'Funzione disattivata dai criteri di sistema.';
$labels['hestia_err_12'] = 'Errore di parsing.';
$labels['hestia_err_13'] = 'Errore del disco.';
$labels['hestia_err_14'] = 'Carico del server troppo elevato.';
$labels['hestia_err_15'] = 'Errore di connessione.';
$labels['hestia_err_16'] = 'Errore FTP.';
$labels['hestia_err_17'] = 'Errore del database.';
$labels['hestia_err_18'] = 'Errore RRD.';
$labels['hestia_err_19'] = 'Errore di aggiornamento.';
$labels['hestia_err_20'] = 'Errore di riavvio.';
?>

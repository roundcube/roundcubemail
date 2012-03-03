<?php

$labels = array();
$labels['changepasswd']  = 'Cambia la Password';
$labels['curpasswd']  = 'Password corrente:';
$labels['newpasswd']  = 'Nuova Password:';
$labels['confpasswd']  = 'Conferma la Nuova Password:';

$messages = array();
$messages['nopassword'] = 'Per favore inserisci la nuova password.';
$messages['nocurpassword'] = 'Per favore inserisci la password corrente.';
$messages['passwordincorrect'] = 'Password corrente sbagliata.';
$messages['passwordinconsistency'] = 'Le password non coincidono, inserirle di nuovo.';
$messages['crypterror'] = 'Non posso salvare la password, funzione di cifratura assente.';
$messages['connecterror'] = 'Non posso salvare la password, errore di connessione.';
$messages['internalerror'] = 'Non posso salvare la password.';
$messages['passwordshort'] = 'La nuova password deve essere lunga almeno $length caratteri.';
$messages['passwordweak'] = 'La nuova password deve contenere almeno una cifra e un segno di punteggiatura.';
$messages['passwordforbidden'] = 'La password scelta contiene dei caratteri non consentiti.';

?>

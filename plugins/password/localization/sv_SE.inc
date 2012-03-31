<?php

$labels = array();
$labels['changepasswd']  = 'Ändra lösenord';
$labels['curpasswd']  = 'Nuvarande lösenord:';
$labels['newpasswd']  = 'Nytt lösenord:';
$labels['confpasswd']  = 'Bekräfta nytt lösenord:';

$messages = array();
$messages['nopassword'] = 'Vänligen ange nytt lösenord.';
$messages['nocurpassword'] = 'Vänligen ange nuvarande lösenord.';
$messages['passwordincorrect'] = 'Felaktigt nuvarande lösenord.';
$messages['passwordinconsistency'] = 'Nya lösenordet och bekräftelsen överensstämmer inte, försök igen.';
$messages['crypterror'] = 'Lösenordet kunde inte ändras. Krypteringsfunktionen saknas.';
$messages['connecterror'] = 'Lösenordet kunde inte ändras. Anslutningen misslyckades.';
$messages['internalerror'] = 'Lösenordet kunde inte ändras.';
$messages['passwordshort'] = 'Lösenordet måste vara minst $length tecken långt.';
$messages['passwordweak'] = 'Lösenordet måste innehålla minst en siffra och ett specialtecken.';
$messages['passwordforbidden'] = 'Lösenordet innehåller otillåtna tecken.';

?>
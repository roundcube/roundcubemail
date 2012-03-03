<?php

$labels = array();
$labels['changepasswd']  = 'Cambiar contrasinal';
$labels['curpasswd']  = 'Contrasinal actual:';
$labels['newpasswd']  = 'Contrasinal novo:';
$labels['confpasswd']  = 'Confirmar contrasinal:';

$messages = array();
$messages['nopassword'] = 'Por favor, introduza un contrasinal novo.';
$messages['nocurpassword'] = 'Por favor, introduza o contrasinal actual.';
$messages['passwordincorrect'] = 'O contrasinal actual é incorrecto.';
$messages['passwordinconsistency'] = 'Os contrasinals non coinciden. Por favor, inténteo de novo.';
$messages['crypterror'] = 'Non foi posible gardar o contrasinal novo. Falta a función de cifrado.';
$messages['connecterror'] = 'Non foi posible gardar o contrasinal novo. Erro de conexión';
$messages['internalerror'] = 'Non foi posible gardar o contrasinal novo.';
$messages['passwordshort'] = 'O contrasinal debe ter polo menos $length caracteres.';
$messages['passwordweak'] = 'O contrasinal debe incluir polo menos un número e un signo de puntuación.';
$messages['passwordforbidden'] = 'O contrasinal contén caracteres non permitidos.';

?>

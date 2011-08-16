<?php

$labels = array();
$labels['changepasswd']  = 'Cambiar contraseña';
$labels['curpasswd']  = 'Contraseña actual:';
$labels['newpasswd']  = 'Contraseña nueva:';
$labels['confpasswd']  = 'Confirmar contraseña:';

$messages = array();
$messages['nopassword'] = 'Por favor introduzca una contraseña nueva.';
$messages['nocurpassword'] = 'Por favor introduzca la contraseña actual.';
$messages['passwordincorrect'] = 'La contraseña actual es incorrecta.';
$messages['passwordinconsistency'] = 'Las contraseñas no coinciden. Por favor, inténtelo de nuevo.';
$messages['crypterror'] = 'No se pudo guardar la contraseña nueva. Falta la función de cifrado.';
$messages['connecterror'] = 'No se pudo guardar la contraseña nueva. Error de conexión';
$messages['internalerror'] = 'No se pudo guardar la contraseña nueva.';
$messages['passwordshort'] = 'La contraseña debe tener por lo menos $length caracteres.';
$messages['passwordweak'] = 'La contraseña debe incluir al menos un número y un signo de puntuación.';
$messages['passwordforbidden'] = 'La contraseña introducida contiene caracteres no permitidos.';

?>

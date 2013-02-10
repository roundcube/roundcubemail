<?php

/*
 +-----------------------------------------------------------------------+
 | plugins/password/localization/<lang>.inc                              |
 |                                                                       |
 | Localization file of the Roundcube Webmail Password plugin            |
 | Copyright (C) 2012-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/

$labels = array();
$labels['changepasswd']  = 'Changer le mot de passe';
$labels['curpasswd']  = 'Mot de passe actuel:';
$labels['newpasswd']  = 'Nouveau mot de passe:';
$labels['confpasswd']  = 'Confirmez le nouveau mot de passe:';

$messages = array();
$messages['nopassword'] = 'Veuillez saisir le nouveau mot de passe.';
$messages['nocurpassword'] = 'Veuillez saisir le mot de passe actuel.';
$messages['passwordincorrect'] = 'Mot de passe actuel incorrect.';
$messages['passwordinconsistency'] = 'Les nouveaux mots de passe ne correspondent pas, veuillez réessayer.';
$messages['crypterror'] = 'Impossible d\'enregistrer le nouveau mot de passe. Fonction de cryptage manquante.';
$messages['connecterror'] = 'Impossible d\'enregistrer le nouveau mot de passe. Erreur de connexion au serveur.';
$messages['internalerror'] = 'Impossible d\'enregistrer le nouveau mot de passe.';
$messages['passwordshort'] = 'Le mot de passe doit être composé d\'au moins $length caractères.';
$messages['passwordweak'] = 'Le mot de passe doit contenir au moins un chiffre et un signe de ponctuation.';
$messages['passwordforbidden'] = 'Le mot de passe contient des caractères interdits.';

?>

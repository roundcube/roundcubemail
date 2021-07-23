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

$labels['changepasswd']  = 'Changer le mot de passe';
$labels['curpasswd']  = 'Mot de passe actuel :';
$labels['newpasswd']  = 'Nouveau mot de passe :';
$labels['confpasswd']  = 'Confirmer le nouveau mot de passe :';
$messages['nopassword'] = 'Veuillez saisir le nouveau mot de passe.';
$messages['nocurpassword'] = 'Veuillez saisir le mot de passe actuel.';
$messages['passwordincorrect'] = 'Le mot de passe actuel est erroné.';
$messages['passwordinconsistency'] = 'Les mots de passe ne correspondent pas, veuillez réessayer.';
$messages['crypterror'] = 'Impossible d’enregistrer le nouveau mot de passe. La fonction de chiffrement est manquante.';
$messages['connecterror'] = 'Impossible d’enregistrer le nouveau mot de passe. Erreur de connexion.';
$messages['internalerror'] = 'Impossible d’enregistrer le nouveau mot de passe.';
$messages['passwordshort'] = 'Le mot de passe doit comporter au moins $length caractères.';
$messages['passwordweak'] = 'Le mot de passe doit comporter au moins un chiffre et un signe de ponctuation.';
$messages['passwordtooweak'] = 'Le mot de passe est trop faible.';
$messages['passwordnoseq'] = 'Le mot de passe ne devrait pas être une séquence comme 123456, QWERTY ou AZERTY.';
$messages['passwordnocommon'] = 'Le mot de passe ne devrait pas être un nom commun ou un nom.';
$messages['passwordforbidden'] = 'Le mot de passe contient des caractères interdits.';
$messages['firstloginchange'] = 'Ceci est votre première connexion. Veuillez changer votre mot de passe.';
$messages['disablednotice'] = 'Le système est en cours de maintenance et les changements de mot de passe sont impossibles pour l’instant. Tout devrait redevenir normal sous peu. Nous regrettons tout inconvénient que cette situation pourrait occasionner.';
$messages['passwinhistory'] = 'Ce mot de passe a déjà été utilisé précédemment.';
$messages['samepasswd'] = 'Le nouveau mot de passe doit être différent de l’ancien.';
$messages['passwdexpirewarning'] = 'Avertissement ! Votre mot de passe arrivera prochainement à expiration. Changez-le avant le $expirationdatetime.';
$messages['passwdexpired'] = 'Votre mot de passe est expiré, vous devez le changer maintenant';
$messages['passwdconstraintviolation'] = 'Contrainte non respectée. Le mot de passe est probablement trop faible.';
$messages['pwned_mustnotbedisclosed'] = 'Le mot de passe ne doit pas être <a href="$href" target="_blank">notoire</a>.';
$messages['pwned_isdisclosed'] = 'Ce mot de passe est connu par ailleurs.';
$messages['pwned_fetcherror'] = 'Échec de vérification de la robustesse du mot de passe.';

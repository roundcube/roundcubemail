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

$labels['hestia_err_policy_short'] = 'Mot de passe trop court (longueur minimale : %min).';
$labels['hestia_err_endpoint_invalid'] = 'Le point de terminaison Hestia n\'est pas configuré ou n\'est pas valide.';
$labels['hestia_err_identity'] = 'Impossible d\'analyser le nom d\'utilisateur dans local@domaine%hint.';
$labels['hestia_err_owner_unknown'] = 'Impossible de déterminer le propriétaire du domaine pour %domain.';
$labels['hestia_err_unknown'] = 'Erreur inconnue de l\'API Hestia.';
$labels['hestia_err_default'] = 'Hestia a renvoyé le code d\'erreur %code.';
$labels['hestia_err_1'] = 'Les arguments fournis ne sont pas suffisants.';
$labels['hestia_err_2'] = 'Objet ou argument non valide.';
$labels['hestia_err_3'] = 'L\'objet n\'existe pas (boîte aux lettres ou domaine introuvable).';
$labels['hestia_err_4'] = 'L\'objet existe déjà.';
$labels['hestia_err_5'] = 'L\'objet est déjà suspendu.';
$labels['hestia_err_6'] = 'L\'objet n\'est pas encore suspendu.';
$labels['hestia_err_7'] = 'Objet en cours d\'utilisation ; ne peut être modifié.';
$labels['hestia_err_8'] = 'Opération bloquée par les limites du paquet.';
$labels['hestia_err_9'] = 'Mot de passe incorrect ou invalide.';
$labels['hestia_err_10'] = 'Permission refusée par Hestia (l\'identité API n\'a pas de droits ou l\'IP n\'est pas autorisée).';
$labels['hestia_err_11'] = 'Fonctionnalité désactivée par la politique du système.';
$labels['hestia_err_12'] = 'Erreur d\'analyse.';
$labels['hestia_err_13'] = 'Erreur de disque.';
$labels['hestia_err_14'] = 'La charge du serveur est trop élevée.';
$labels['hestia_err_15'] = 'Erreur de connexion.';
$labels['hestia_err_16'] = 'Erreur FTP.';
$labels['hestia_err_17'] = 'Erreur de base de données.';
$labels['hestia_err_18'] = 'Erreur RRD.';
$labels['hestia_err_19'] = 'Erreur de mise à jour.';
$labels['hestia_err_20'] = 'Erreur de redémarrage.';
?>

<?php

/*

+-----------------------------------------------------------------------+
| language/fr_FR/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Joel S. <joel.s@spijoprod.com>                                |
|         Jerome Lipowicz <jerome@yayel.com>                            |
|         Maximilien Cuony <theglu@theglu.org>                          |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Erreur d\'authentification';
$messages['cookiesdisabled'] = 'Votre navigateur n\'accepte pas les cookies';
$messages['sessionerror'] = 'Votre session est invalide ou a expiré';
$messages['imaperror'] = 'Erreur de connexion au serveur IMAP';
$messages['servererror'] = 'Erreur Serveur !';
$messages['invalidrequest'] = 'Requête invalide ! Aucune donnée n\'a été sauvegardée.';
$messages['nomessagesfound'] = 'Aucun message trouvé dans cette boîte aux lettres';
$messages['loggedout'] = 'Vous venez de vous déconnecter avec succès. Au revoir !';
$messages['mailboxempty'] = 'Boîte aux lettres vide';
$messages['loading'] = 'Chargement...';
$messages['uploading'] = 'Envoi du fichier...';
$messages['loadingdata'] = 'Chargement des données...';
$messages['checkingmail'] = 'Vérification des nouveaux messages...';
$messages['sendingmessage'] = 'Message en cours d\'envoi...';
$messages['messagesent'] = 'Message bien envoyé';
$messages['savingmessage'] = 'Sauvegarde du message...';
$messages['messagesaved'] = 'Message sauvegardé dans Brouillons';
$messages['successfullysaved'] = 'Sauvegarde effectuée';
$messages['addedsuccessfully'] = 'Contact ajouté dans le carnet d\'adresses';
$messages['contactexists'] = 'Un contact avec ce courriel existe déjà';
$messages['blockedimages'] = 'Afin de préserver votre vie privée, les images distantes ont été bloquées dans ce message.';
$messages['encryptedmessage'] = 'Ceci est un message crypté et il ne peut pas être affiché. Désolé !';
$messages['nocontactsfound'] = 'Aucun contact n\'a pu être trouvé';
$messages['contactnotfound'] = 'Le contact demandé n\'a pas été trouvé';
$messages['sendingfailed'] = 'L\'envoi du message a échoué';
$messages['senttooquickly'] = 'Vous devez attendre $sec sec. pour envoyer le message';
$messages['errorsavingsent'] = 'Une erreur est survenue pendant la sauvegarde du message envoyé';
$messages['errorsaving'] = 'Une erreur a empêché la sauvegarde';
$messages['errormoving'] = 'Impossible de déplacer le message';
$messages['errorcopying'] = 'Le(s) message(s) n\'a/ont pas pu être copié(s)';
$messages['errordeleting'] = 'Impossible d\'effacer le message';
$messages['errormarking'] = 'Impossible de marquer le message';
$messages['deletecontactconfirm'] = 'Voulez-vous vraiment effacer le(s) contact(s) sélectionné(s) ?';
$messages['deletemessagesconfirm'] = 'Voulez-vous vraiment supprimer le(s) message(s) sélectionné(s) ?';
$messages['deletefolderconfirm'] = 'Voulez-vous vraiment effacer ce dossier ?';
$messages['purgefolderconfirm'] = 'Voulez-vous vraiment effacer tous les messages de ce dossier ?';
$messages['foldercreating'] = 'Création du dossier...';
$messages['folderdeleting'] = 'Suppression du dossier...';
$messages['folderrenaming'] = 'Renommage du dossier...';
$messages['foldermoving'] = 'Déplacement du dossier...';
$messages['formincomplete'] = 'Le formulaire n\'a pas été entièrement rempli';
$messages['noemailwarning'] = 'Veuillez spécifier un courriel valide';
$messages['nonamewarning'] = 'Veuillez fournir un nom';
$messages['nopagesizewarning'] = 'Veuillez indiquer une taille de page';
$messages['nosenderwarning'] = 'Veuillez renseigner l\'adresse d\'expéditeur';
$messages['norecipientwarning'] = 'Veuillez ajouter au moins un destinataire';
$messages['nosubjectwarning'] = 'Le champ « Objet » est vide. Souhaitez-vous le renseigner maintenant ?';
$messages['nobodywarning'] = 'Envoyer ce message sans texte ?';
$messages['notsentwarning'] = 'Le message n\'a pas été envoyé. Voulez-vous abandonner ce message ?';
$messages['noldapserver'] = 'Choisissez un serveur LDAP pour la recherche';
$messages['nocontactsreturned'] = 'Aucun contact trouvé';
$messages['nosearchname'] = 'Entrez un nom de contact ou un courriel';
$messages['notuploadedwarning'] = 'Toutes les pièces jointes ne sont pas encore envoyées pour le moment. Merci d\'attendre ou d\'annuler l\'envoi.';
$messages['searchsuccessful'] = '$nr messages trouvés';
$messages['searchnomatch'] = 'La recherche ne donne aucun résultat';
$messages['searching'] = 'En cours de recherche...';
$messages['checking'] = 'Vérification...';
$messages['nospellerrors'] = 'Aucune faute trouvée';
$messages['folderdeleted'] = 'Dossier effacé';
$messages['deletedsuccessfully'] = 'Supprimé(s) avec succès';
$messages['converting'] = 'Suppression de la mise en forme...';
$messages['messageopenerror'] = 'Impossible de charger le message depuis serveur';
$messages['fileuploaderror'] = 'Transfert du fichier échoué';
$messages['filesizeerror'] = 'Le fichier transféré dépasse la taille maximale de $size';
$messages['copysuccess'] = 'Succès de la copie des $nr adresses';
$messages['copyerror'] = 'Ne peut pas copier les adresses';
$messages['sourceisreadonly'] = 'Cette source d\'adresse est en lecture seule';
$messages['errorsavingcontact'] = 'Ne peut pas enregistrer l\'adresse du contact';
$messages['movingmessage'] = 'Déplacement du message...';
$messages['copyingmessage'] = 'Copie du message ...';
$messages['receiptsent'] = 'L\'accusé de réception a bien été envoyé';
$messages['errorsendingreceipt'] = 'L\'accusé de réception n\'a pas pu être envoyé';
$messages['nodeletelastidentity'] = 'Vous ne pouvez pas effacer votre seule identité.';
$messages['addsubfolderhint'] = 'Ce dossier sera créé comme sous-dossier de celui sélectionné.';
$messages['forbiddencharacter'] = 'Le nom du dossier contient un caractère interdit';
$messages['selectimportfile'] = 'Veuillez sélectionner un fichier à envoyer';
$messages['addresswriterror'] = 'Impossible d\'écrire dans le carnet d\'adresse sélectionné';
$messages['contactaddedtogroup'] = 'Les contacts ont bien été ajoutés à ce groupe';
$messages['contactremovedfromgroup'] = 'Les contacts ont bien été supprimés de ce groupe';
$messages['importwait'] = 'Importation, veuillez patienter...';
$messages['importerror'] = 'Importation échouée ! Le fichier envoyé n\'est pas un fichier vCard valide.';
$messages['importconfirm'] = '<b>$inserted contacts importés avec succès, $skipped entrées existantes ignorées</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Cette opération n\'est pas permise !';
$messages['nofromaddress'] = 'Il manque une adresse e-mail dans l\'identitée sélectionnée';
$messages['editorwarning'] = 'Passer à l\'éditeur texte seul causera la perte du formatage du texte. Voulez-vous continuer ?';
$messages['httpreceivedencrypterror'] = 'Une erreur fatale de configuration est survenue. Veuillez contacter votre administrateur immédiatement. <b>Votre message n\'a pas pu être envoyé.</b>';
$messages['smtpconnerror'] = 'Erreur SMTP ($code): Echec de la connexion au serveur';
$messages['smtpautherror'] = 'Erreur SMTP ($code): Echec de l\'authentification';
$messages['smtpfromerror'] = 'Erreur SMTP ($code): Impossible de définir l\'expéditeur "$from"';
$messages['smtptoerror'] = 'Erreur SMTP ($code): Impossible d\'ajouter le destinataire "$to"';
$messages['smtprecipientserror'] = 'Erreur SMTP: Impossible de lire la liste des destinataires';
$messages['smtperror'] = 'Erreur SMTP: $msg';
$messages['emailformaterror'] = 'Adresse email incorrecte: $email';
$messages['toomanyrecipients'] = 'Trop de destinataires. Réduisez leur nombre à $max maximum.';
$messages['maxgroupmembersreached'] = 'Le nombre de membres du groupe dépasse le maximum de $max.';

?>

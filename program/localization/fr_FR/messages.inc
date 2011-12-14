<?php

/*

+-----------------------------------------------------------------------+
| language/fr_FR/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2011, The Roundcube Dev Team                       |
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
$messages['servererrormsg'] = 'Erreur du serveur: $msg';
$messages['dberror'] = 'Erreur avec la base de donnée!';
$messages['errorreadonly'] = 'Impossible d\'effectuer cette opération. Le dossier est en lecture seule';
$messages['errornoperm'] = 'Impossible d\'effectuer cette opération. Permission refusée';
$messages['invalidrequest'] = 'Requête invalide ! Aucune donnée n\'a été sauvegardée.';
$messages['nomessagesfound'] = 'Aucun message trouvé dans cette boîte aux lettres';
$messages['loggedout'] = 'Vous venez de vous déconnecter avec succès. Au revoir !';
$messages['mailboxempty'] = 'Boîte aux lettres vide';
$messages['loading'] = 'Chargement...';
$messages['uploading'] = 'Envoi du fichier...';
$messages['uploadingmany'] = 'Envoi des fichiers ...';
$messages['loadingdata'] = 'Chargement des données...';
$messages['checkingmail'] = 'Vérification des nouveaux messages...';
$messages['sendingmessage'] = 'Message en cours d\'envoi...';
$messages['messagesent'] = 'Message bien envoyé';
$messages['savingmessage'] = 'Sauvegarde du message...';
$messages['messagesaved'] = 'Message sauvegardé dans Brouillons';
$messages['successfullysaved'] = 'Sauvegarde effectuée';
$messages['addedsuccessfully'] = 'Contact ajouté dans le carnet d\'adresses';
$messages['contactexists'] = 'Un contact avec ce courriel existe déjà';
$messages['contactnameexists'] = 'Un contact avec le même nom existe déjà.';
$messages['blockedimages'] = 'Afin de préserver votre vie privée, les images distantes ont été bloquées dans ce message.';
$messages['encryptedmessage'] = 'Ceci est un message crypté et il ne peut pas être affiché. Désolé !';
$messages['nocontactsfound'] = 'Aucun contact n\'a pu être trouvé';
$messages['contactnotfound'] = 'Le contact demandé n\'a pas été trouvé';
$messages['contactsearchonly'] = 'Entrez un ou plusieurs mots clés pour trouver des contacts';
$messages['sendingfailed'] = 'L\'envoi du message a échoué';
$messages['senttooquickly'] = 'Vous devez attendre $sec sec. pour envoyer le message';
$messages['errorsavingsent'] = 'Une erreur est survenue pendant la sauvegarde du message envoyé';
$messages['errorsaving'] = 'Une erreur a empêché la sauvegarde';
$messages['errormoving'] = 'Impossible de déplacer le message';
$messages['errorcopying'] = 'Le(s) message(s) n\'a/ont pas pu être copié(s)';
$messages['errordeleting'] = 'Impossible d\'effacer le message';
$messages['errormarking'] = 'Impossible de marquer le message';
$messages['deletecontactconfirm'] = 'Voulez-vous vraiment effacer le(s) contact(s) sélectionné(s) ?';
$messages['deletegroupconfirm'] = 'Êtes-vous sûr de vouloir supprimer le groupe sélectionné ?';
$messages['deletemessagesconfirm'] = 'Voulez-vous vraiment supprimer le(s) message(s) sélectionné(s) ?';
$messages['deletefolderconfirm'] = 'Voulez-vous vraiment effacer ce dossier ?';
$messages['purgefolderconfirm'] = 'Voulez-vous vraiment effacer tous les messages de ce dossier ?';
$messages['contactdeleting'] = 'Suppression de contact(s)...';
$messages['groupdeleting'] = 'Suppression du groupe ...';
$messages['folderdeleting'] = 'Suppression du dossier...';
$messages['foldermoving'] = 'Déplacement du dossier...';
$messages['foldersubscribing'] = 'Inscription du dossier...';
$messages['folderunsubscribing'] = 'Désinscription du dossier...';
$messages['formincomplete'] = 'Le formulaire n\'a pas été entièrement rempli';
$messages['noemailwarning'] = 'Veuillez spécifier un courriel valide';
$messages['nonamewarning'] = 'Veuillez fournir un nom';
$messages['nopagesizewarning'] = 'Veuillez indiquer une taille de page';
$messages['nosenderwarning'] = 'Veuillez renseigner l\'adresse d\'expéditeur';
$messages['norecipientwarning'] = 'Veuillez ajouter au moins un destinataire';
$messages['nosubjectwarning'] = 'Le champ «Objet» est vide. Souhaitez-vous le renseigner maintenant ?';
$messages['nobodywarning'] = 'Envoyer ce message sans texte ?';
$messages['notsentwarning'] = 'Le message n\'a pas été envoyé. Voulez-vous abandonner ce message ?';
$messages['noldapserver'] = 'Choisissez un serveur LDAP pour la recherche';
$messages['nosearchname'] = 'Entrez un nom de contact ou un courriel';
$messages['notuploadedwarning'] = 'Toutes les pièces jointes ne sont pas encore envoyées pour le moment. Merci d\'attendre ou d\'annuler l\'envoi.';
$messages['searchsuccessful'] = '$nr messages trouvés';
$messages['contactsearchsuccessful'] = '$nr contact(s) trouvé(s).';
$messages['searchnomatch'] = 'La recherche ne donne aucun résultat';
$messages['searching'] = 'En cours de recherche...';
$messages['checking'] = 'Vérification...';
$messages['nospellerrors'] = 'Aucune faute trouvée';
$messages['folderdeleted'] = 'Dossier effacé';
$messages['foldersubscribed'] = 'Le dossier a bien été inscrit';
$messages['folderunsubscribed'] = 'Le dossier a bien été désinscrit';
$messages['folderpurged'] = 'Le dossier a bien été vidé';
$messages['folderexpunged'] = 'Le dossier a bien été compacté';
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
$messages['copyingcontact'] = 'Copie des contacts ...';
$messages['deletingmessage'] = 'Suppression des messages...';
$messages['markingmessage'] = 'Marquage des messages...';
$messages['addingmember'] = 'Ajout des contacts dans le groupe ...';
$messages['removingmember'] = 'Suppression des contacts du groupe ...';
$messages['receiptsent'] = 'L\'accusé de réception a bien été envoyé';
$messages['errorsendingreceipt'] = 'L\'accusé de réception n\'a pas pu être envoyé';
$messages['nodeletelastidentity'] = 'Vous ne pouvez pas effacer votre seule identité.';
$messages['forbiddencharacter'] = 'Le nom du dossier contient un caractère interdit';
$messages['selectimportfile'] = 'Veuillez sélectionner un fichier à envoyer';
$messages['addresswriterror'] = 'Impossible d\'écrire dans le carnet d\'adresse sélectionné';
$messages['contactaddedtogroup'] = 'Les contacts ont bien été ajoutés à ce groupe';
$messages['contactremovedfromgroup'] = 'Les contacts ont bien été supprimés de ce groupe';
$messages['importwait'] = 'Importation, veuillez patienter...';
$messages['importerror'] = 'Importation échouée ! Le fichier envoyé n\'est pas un fichier vCard valide.';
$messages['importconfirm'] = '<b>$inserted contacts importés avec succès, $skipped entrées existantes ignorées</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>$skipped entrée(s) déjà existante(s)</b>';
$messages['opnotpermitted'] = 'Cette opération n\'est pas permise !';
$messages['nofromaddress'] = 'Il manque une adresse e-mail dans l\'identité sélectionnée';
$messages['editorwarning'] = 'Passer à l\'éditeur texte seul causera la perte du formatage du texte. Voulez-vous continuer ?';
$messages['httpreceivedencrypterror'] = 'Une erreur fatale de configuration est survenue. Veuillez contacter votre administrateur immédiatement. <b>Votre message n\'a pas pu être envoyé.</b>';
$messages['smtpconnerror'] = 'Erreur SMTP ($code): Echec de la connexion au serveur';
$messages['smtpautherror'] = 'Erreur SMTP ($code): Echec de l\'authentification';
$messages['smtpfromerror'] = 'Erreur SMTP ($code): Impossible de définir l\'expéditeur "$from" ($msg)';
$messages['smtptoerror'] = 'Erreur SMTP ($code): Impossible d\'ajouter le destinataire "$to" ($msg)';
$messages['smtprecipientserror'] = 'Erreur SMTP: Impossible de lire la liste des destinataires';
$messages['smtperror'] = 'Erreur SMTP: $msg';
$messages['emailformaterror'] = 'Adresse email incorrecte: $email';
$messages['toomanyrecipients'] = 'Trop de destinataires. Réduisez leur nombre à $max maximum.';
$messages['maxgroupmembersreached'] = 'Le nombre de membres du groupe dépasse le maximum de $max.';
$messages['internalerror'] = 'Une erreur interne est survenue. Merci de réessayer';
$messages['contactdelerror'] = 'Les contacts n\'ont pas pu être supprimés';
$messages['contactdeleted'] = 'Les contacts ont bien été supprimés';
$messages['contactrestoreerror'] = 'Impossible de restaurer les contacts supprimés.';
$messages['contactrestored'] = 'Les contacts ont bien été restaurés.';
$messages['groupdeleted'] = 'Le groupe a bien été supprimé';
$messages['grouprenamed'] = 'Le groupe a bien été renommé';
$messages['groupcreated'] = 'Le groupe a bien été créé';
$messages['savedsearchdeleted'] = 'La recherche enregistrée a bien été supprimée.';
$messages['savedsearchdeleteerror'] = 'Impossible de supprimer la recherche enregistrée.';
$messages['savedsearchcreated'] = 'La recherche enregistrée a bien été crée.';
$messages['savedsearchcreateerror'] = 'Impossible de créer la recherche enregistrée.';
$messages['messagedeleted'] = 'Les messages ont bien été supprimés';
$messages['messagemoved'] = 'Les messages ont bien été déplacés';
$messages['messagecopied'] = 'Les messages ont bien été copiés';
$messages['messagemarked'] = 'Les messages ont bien été marqués';
$messages['autocompletechars'] = 'Entrez au moins $min caractères pour l\'auto-complétion';
$messages['autocompletemore'] = 'Plusieurs entrées trouvées. Tapez plus de caractères.';
$messages['namecannotbeempty'] = 'Le nom ne peut pas être vide';
$messages['nametoolong'] = 'Le nom est trop long';
$messages['folderupdated'] = 'Le dossier a bien été mis à jour';
$messages['foldercreated'] = 'Le dossier a bien été créé';
$messages['invalidimageformat'] = 'Format d\'image invalide';
$messages['mispellingsfound'] = 'Des fautes d\'orthographe ont été détectées dans le message.';
$messages['parentnotwritable'] = 'Impossible de créer/déplacer le dossier dans le dossier parent sélectionné. Aucun droit d\'accès.';
$messages['messagetoobig'] = 'Le message est trop gros pour être traité.';

?>
<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/messages.inc                                      |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/messages/
*/

$messages = array();
$messages['errortitle']  = 'Une erreur est survenue !';
$messages['loginfailed']  = 'L\'authentification a échoué';
$messages['cookiesdisabled'] = 'Votre navigateur n\'accepte pas les cookies';
$messages['sessionerror'] = 'Votre session est invalide ou a expiré';
$messages['storageerror'] = 'Erreur de connexion au serveur IMAP';
$messages['servererror'] = 'Erreur Serveur !';
$messages['servererrormsg'] = 'Erreur du serveur: $msg';
$messages['dberror'] = 'Erreur avec la base de donnée!';
$messages['requesttimedout'] = 'Délai de la requête expiré';
$messages['errorreadonly'] = 'Impossible d\'effectuer cette opération. Le dossier est en lecture seule';
$messages['errornoperm'] = 'Impossible d\'effectuer cette opération. Permission refusée';
$messages['erroroverquota'] = 'Impossible d\'effectuer cette opération. Plus d\'espace libre.';
$messages['erroroverquotadelete'] = 'Plus d\'espace libre. Utilisez SHIFT+DEL pour supprimer un message.';
$messages['invalidrequest'] = 'Requête invalide ! Aucune donnée n\'a été sauvegardée.';
$messages['invalidhost'] = 'Nom du serveur invalide.';
$messages['nomessagesfound'] = 'Cette boîte aux lettres ne contient aucun message.';
$messages['loggedout'] = 'Vous venez de vous déconnecter avec succès. Au revoir !';
$messages['mailboxempty'] = 'La boîte aux lettres est vide.';
$messages['refreshing'] = 'Rafraîchissement en cours...';
$messages['loading'] = 'Chargement...';
$messages['uploading'] = 'Envoi du fichier...';
$messages['uploadingmany'] = 'Envoi des fichiers ...';
$messages['loadingdata'] = 'Chargement des données...';
$messages['checkingmail'] = 'Vérification des nouveaux messages...';
$messages['sendingmessage'] = 'Expédition du message...';
$messages['messagesent'] = 'Message expédié.';
$messages['savingmessage'] = 'Sauvegarde du message...';
$messages['messagesaved'] = 'Message sauvegardé dans Brouillons';
$messages['successfullysaved'] = 'Sauvegarde effectuée';
$messages['addedsuccessfully'] = 'Contact ajouté dans le carnet d\'adresses';
$messages['contactexists'] = 'Cette adresse courriel est utilisée par un autre contact';
$messages['contactnameexists'] = 'Il existe déjà un contact nommé ainsi.';
$messages['blockedimages'] = 'Les images distantes sont bloquées pour protéger votre vie privée.';
$messages['encryptedmessage'] = 'Désolé, ce message est chiffré et ne peut être affiché.';
$messages['nocontactsfound'] = 'Aucun contact n\'a pu être trouvé';
$messages['contactnotfound'] = 'Le contact demandé n\'a pas été trouvé.';
$messages['contactsearchonly'] = 'Entrez un ou plusieurs mots clés pour trouver des contacts';
$messages['sendingfailed'] = 'L\'envoi du message a échoué';
$messages['senttooquickly'] = 'Vous devez attendre $sec s. pour envoyer le message';
$messages['errorsavingsent'] = 'Une erreur est survenue pendant la sauvegarde du message envoyé';
$messages['errorsaving'] = 'Une erreur a empêché la sauvegarde';
$messages['errormoving'] = 'Impossible de déplacer le message';
$messages['errorcopying'] = 'La copie de ce(s) message(s) a échoué.';
$messages['errordeleting'] = 'Impossible d\'effacer le message';
$messages['errormarking'] = 'Impossible de marquer le message';
$messages['deletecontactconfirm']  = 'Êtes-vous sûr de vouloir supprimer le(s) contact(s) sélectionné(s) ?';
$messages['deletegroupconfirm']  = 'Êtes-vous sûr de vouloir supprimer le groupe sélectionné ?';
$messages['deletemessagesconfirm'] = 'Êtes-vous sûr de vouloir supprimer le(s) message(s) sélectionné(s) ?';
$messages['deletefolderconfirm']  = 'Êtes-vous sûr de vouloir supprimer ce dossier ?';
$messages['purgefolderconfirm']  = 'Êtes-vous sûr de vouloir supprimer tous les messages de ce dossier ?';
$messages['contactdeleting'] = 'Suppression de contact(s)...';
$messages['groupdeleting'] = 'Suppression du groupe ...';
$messages['folderdeleting'] = 'Suppression du dossier...';
$messages['foldermoving'] = 'Déplacement du dossier...';
$messages['foldersubscribing'] = 'Inscription du dossier...';
$messages['folderunsubscribing'] = 'Désinscription du dossier...';
$messages['formincomplete'] = 'Le formulaire n\'a pas été entièrement rempli';
$messages['noemailwarning'] = 'Veuillez spécifier un courriel valide';
$messages['nonamewarning']  = 'Veuillez fournir un nom';
$messages['nopagesizewarning'] = 'Veuillez indiquer une taille de page';
$messages['nosenderwarning'] = 'Veuillez renseigner l\'adresse d\'expéditeur';
$messages['norecipientwarning'] = 'Veuillez ajouter au moins un destinataire';
$messages['nosubjectwarning']  = 'Le champ «Objet» est vide. Souhaitez-vous le renseigner maintenant ?';
$messages['nobodywarning'] = 'Envoyer ce message sans texte ?';
$messages['notsentwarning'] = 'Le message n\'a pas été envoyé. Voulez-vous abandonner ce message ?';
$messages['noldapserver'] = 'Choisissez un serveur LDAP pour la recherche';
$messages['nosearchname'] = 'Entrez un nom de contact ou un courriel';
$messages['notuploadedwarning'] = 'Certaines pièces jointes sont en cours d\'expédition : attendez ou annulez l\'envoi.';
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
$messages['deleteidentityconfirm'] = 'Voulez vous vraiment supprimer cette identités ?';
$messages['nodeletelastidentity'] = 'Vous ne pouvez pas effacer votre seule identité.';
$messages['forbiddencharacter'] = 'Le nom du dossier contient un caractère interdit';
$messages['selectimportfile'] = 'Veuillez sélectionner un fichier à envoyer';
$messages['addresswriterror'] = 'Impossible d\'écrire dans le carnet d\'adresse sélectionné';
$messages['contactaddedtogroup'] = 'Les contacts ont bien été ajoutés à ce groupe';
$messages['contactremovedfromgroup'] = 'Les contacts ont bien été supprimés de ce groupe';
$messages['nogroupassignmentschanged'] = 'Appartenance aux groupes inchangée.';
$messages['importwait'] = 'Importation, veuillez patienter...';
$messages['importformaterror'] = 'Echec de l\'import ! Le fichier n\'est pas un fichier d\'import de données valide.';
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
$messages['attachmentvalidationerror'] = 'ATTENTION ! Cette pièce jointe est suspecte car son type ne correspond pas au type déclaré dans ce message. Si vous ne faites pas confiance à l\'expéditeur, vous ne devriez pas l\'ouvrir dans le navigateur, car il peut contenir des contenus malveillants.<br/><br/><em>Attendu : $expected; trouvé : $detected</em>';
$messages['noscriptwarning'] = 'Atention : Ce service de webmail nécessite Javascript ! Pour pouvoir l\'utiliser, merci d\'activer Javascript dans les préférences de votre navigateur.';

?>

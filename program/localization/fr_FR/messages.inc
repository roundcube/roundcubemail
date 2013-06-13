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
$messages['loginfailed']  = 'L\'authentification a échoué.';
$messages['cookiesdisabled'] = 'Votre navigateur n\'accepte pas les cookies.';
$messages['sessionerror'] = 'Votre session est invalide ou a expiré.';
$messages['storageerror'] = 'Erreur de connexion au serveur IMAP.';
$messages['servererror'] = 'Erreur Serveur !';
$messages['servererrormsg'] = 'Erreur du serveur : $msg';
$messages['dberror'] = 'Erreur avec la base de données !';
$messages['requesttimedout'] = 'Délai de la requête expiré';
$messages['errorreadonly'] = 'Impossible d\'effectuer cette opération. Le dossier est en lecture seule.';
$messages['errornoperm'] = 'Impossible d\'effectuer cette opération. Permission refusée.';
$messages['erroroverquota'] = 'Impossible d\'effectuer cette opération. Plus d\'espace libre.';
$messages['erroroverquotadelete'] = 'Plus d\'espace libre. Utilisez SHIFT+DEL pour supprimer un message.';
$messages['invalidrequest'] = 'Requête invalide ! Aucune donnée n\'a été sauvegardée.';
$messages['invalidhost'] = 'Nom du serveur invalide.';
$messages['nomessagesfound'] = 'Cette boîte aux lettres ne contient aucun message.';
$messages['loggedout'] = 'Vous vous êtes correctement déconnecté. Au revoir !';
$messages['mailboxempty'] = 'La boîte aux lettres est vide.';
$messages['refreshing'] = 'Rafraîchissement en cours...';
$messages['loading'] = 'Chargement en cours...';
$messages['uploading'] = 'Transfert du fichier en cours...';
$messages['uploadingmany'] = 'Transfert des fichiers en cours...';
$messages['loadingdata'] = 'Chargement des données en cours...';
$messages['checkingmail'] = 'Vérification des nouveaux messages...';
$messages['sendingmessage'] = 'Envoi du message en cours...';
$messages['messagesent'] = 'Le message a bien été expédié.';
$messages['savingmessage'] = 'Sauvegarde du message en cours...';
$messages['messagesaved'] = 'Message sauvegardé dans Brouillons.';
$messages['successfullysaved'] = 'La sauvegarde a bien été effectuée.';
$messages['addedsuccessfully'] = 'Le contact a bien été ajouté dans le carnet d\'adresses.';
$messages['contactexists'] = 'Cette adresse courriel est utilisée par un autre contact.';
$messages['contactnameexists'] = 'Il existe déjà un contact nommé ainsi.';
$messages['blockedimages'] = 'Les images distantes sont bloquées pour protéger votre vie privée.';
$messages['encryptedmessage'] = 'Désolé, ce message est chiffré et ne peut être affiché.';
$messages['nocontactsfound'] = 'Aucun contact n\'a pu être trouvé.';
$messages['contactnotfound'] = 'Le contact demandé n\'a pas été trouvé.';
$messages['contactsearchonly'] = 'Entrez un ou plusieurs mots clés pour trouver des contacts.';
$messages['sendingfailed'] = 'L\'envoi du message a échoué.';
$messages['senttooquickly'] = 'Veuillez patienter $sec s. pour envoyer ce message.';
$messages['errorsavingsent'] = 'Une erreur est survenue pendant la sauvegarde du message envoyé.';
$messages['errorsaving'] = 'Une erreur est survenue pendant la sauvegarde.';
$messages['errormoving'] = 'Impossible de déplacer le(s) message(s).';
$messages['errorcopying'] = 'Impossible de copier le(s) message(s).';
$messages['errordeleting'] = 'Impossible de supprimer le(s) message(s).';
$messages['errormarking'] = 'Impossible de marquer le(s) message(s).';
$messages['deletecontactconfirm']  = 'Êtes-vous sûr de vouloir supprimer le(s) contact(s) sélectionné(s) ?';
$messages['deletegroupconfirm']  = 'Êtes-vous sûr de vouloir supprimer le groupe sélectionné ?';
$messages['deletemessagesconfirm'] = 'Êtes-vous sûr de vouloir supprimer le(s) message(s) sélectionné(s) ?';
$messages['deletefolderconfirm']  = 'Êtes-vous sûr de vouloir supprimer ce dossier ?';
$messages['purgefolderconfirm']  = 'Êtes-vous sûr de vouloir supprimer tous les messages de ce dossier ?';
$messages['contactdeleting'] = 'Suppression de contact(s) en cours...';
$messages['groupdeleting'] = 'Suppression du groupe...';
$messages['folderdeleting'] = 'Suppression du dossier...';
$messages['foldermoving'] = 'Déplacement du dossier...';
$messages['foldersubscribing'] = 'Inscription du dossier...';
$messages['folderunsubscribing'] = 'Désinscription du dossier...';
$messages['formincomplete'] = 'Le formulaire n\'a pas été entièrement rempli.';
$messages['noemailwarning'] = 'Veuillez spécifier un courriel valide.';
$messages['nonamewarning']  = 'Veuillez fournir un nom.';
$messages['nopagesizewarning'] = 'Veuillez indiquer une taille de page.';
$messages['nosenderwarning'] = 'Veuillez renseigner l\'adresse d\'expéditeur.';
$messages['norecipientwarning'] = 'Veuillez ajouter au moins un destinataire.';
$messages['nosubjectwarning']  = 'Le champ «Objet» est vide. Souhaitez-vous le renseigner maintenant ?';
$messages['nobodywarning'] = 'Envoyer ce message sans texte ?';
$messages['notsentwarning'] = 'Le message n\'a pas été envoyé. Voulez-vous abandonner ce message ?';
$messages['noldapserver'] = 'Choisissez un serveur LDAP pour la recherche.';
$messages['nosearchname'] = 'Entrez un nom de contact ou un courriel.';
$messages['notuploadedwarning'] = 'Toutes les pièces jointes n\'ont pas encore été transférées. Veuillez patienter ou annuler cette opération.';
$messages['searchsuccessful'] = '$nr message(s) trouvé(s).';
$messages['contactsearchsuccessful'] = '$nr contact(s) trouvé(s).';
$messages['searchnomatch'] = 'La recherche n\'a donné aucun résultat.';
$messages['searching'] = 'Recherche en cours...';
$messages['checking'] = 'Vérification en cours...';
$messages['nospellerrors'] = 'Aucune faute d\'orthographe trouvée.';
$messages['folderdeleted'] = 'Le dossier a bien été effacé.';
$messages['foldersubscribed'] = 'Le dossier a bien été inscrit.';
$messages['folderunsubscribed'] = 'Le dossier a bien été désinscrit.';
$messages['folderpurged'] = 'Le dossier a bien été vidé.';
$messages['folderexpunged'] = 'Le dossier a bien été compacté.';
$messages['deletedsuccessfully'] = 'Correctement supprimé(s).';
$messages['converting'] = 'Suppression de la mise en forme...';
$messages['messageopenerror'] = 'Impossible de charger le message depuis serveur.';
$messages['fileuploaderror'] = 'Transfert du fichier échoué';
$messages['filesizeerror'] = 'Le fichier transféré dépasse la taille maximale de $size.';
$messages['copysuccess'] = 'Les $nr adresses ont bien été copiées.';
$messages['copyerror'] = 'Impossible de copier des adresses.';
$messages['sourceisreadonly'] = 'Cette source d\'adresse est en lecture seule.';
$messages['errorsavingcontact'] = 'Impossible de sauvegarder l\'adresse du contact.';
$messages['movingmessage'] = 'Message(s) en cours de déplacement...';
$messages['copyingmessage'] = 'Message(s) en cours de copie...';
$messages['copyingcontact'] = 'Contact(s) en cours de copie...';
$messages['deletingmessage'] = 'Message(s) en cours de suppression...';
$messages['markingmessage'] = 'Message(s) en cours de marquage...';
$messages['addingmember'] = 'Contact(s) en cours d\'ajout dans le groupe...';
$messages['removingmember'] = 'Contact(s) en cours de suppression du groupe...';
$messages['receiptsent'] = 'L\'accusé de réception a bien été envoyé.';
$messages['errorsendingreceipt'] = 'Impossible d\'envoyer l\'accusé de réception.';
$messages['deleteidentityconfirm'] = 'Voulez vous vraiment supprimer cette identité ?';
$messages['nodeletelastidentity'] = 'Vous ne pouvez pas effacer votre seule identité.';
$messages['forbiddencharacter'] = 'Le nom du dossier contient un caractère interdit.';
$messages['selectimportfile'] = 'Veuillez sélectionner un fichier à transférer.';
$messages['addresswriterror'] = 'Impossible d\'écrire dans le carnet d\'adresse sélectionné.';
$messages['contactaddedtogroup'] = 'Les contacts ont bien été ajoutés à ce groupe.';
$messages['contactremovedfromgroup'] = 'Les contacts ont bien été supprimés de ce groupe.';
$messages['nogroupassignmentschanged'] = 'Appartenance aux groupes inchangée.';
$messages['importwait'] = 'Import en cours, veuillez patienter...';
$messages['importformaterror'] = 'L\'import a échoué ! Le fichier transféré n\'est pas un fichier d\'import de données valide.';
$messages['importconfirm'] = '<b>Les $inserted contacts ont bien été importés</b>';
$messages['importconfirmskipped'] = '<b>$skipped entrée(s) déjà existante(s)</b>';
$messages['opnotpermitted'] = 'Cette opération n\'est pas permise !';
$messages['nofromaddress'] = 'Courriel manquant dans l\'identité sélectionnée.';
$messages['editorwarning'] = 'Passer à l\'éditeur de texte brut causera la perte du formatage du texte. Souhaitez-vous continuer ?';
$messages['httpreceivedencrypterror'] = 'Une erreur fatale de configuration est survenue. Veuillez contacter votre administrateur immédiatement. <b>Votre message n\'a pas pu être envoyé.</b>';
$messages['smtpconnerror'] = 'Erreur SMTP ($code) : Échec de la connexion au serveur.';
$messages['smtpautherror'] = 'Erreur SMTP ($code) : Échec de l\'authentification.';
$messages['smtpfromerror'] = 'Erreur SMTP ($code) : Impossible de définir l\'expéditeur "$from" ($msg)';
$messages['smtptoerror'] = 'Erreur SMTP ($code) : Impossible d\'ajouter le destinataire "$to" ($msg)';
$messages['smtprecipientserror'] = 'Erreur SMTP : Impossible de lire la liste des destinataires.';
$messages['smtperror'] = 'Erreur SMTP : $msg';
$messages['emailformaterror'] = 'Courriel incorrect : $email';
$messages['toomanyrecipients'] = 'Trop de destinataires. Réduisez leur nombre à $max maximum.';
$messages['maxgroupmembersreached'] = 'Le nombre de membres du groupe dépasse le maximum de $max.';
$messages['internalerror'] = 'Une erreur interne est survenue. Veuillez réessayer.';
$messages['contactdelerror'] = 'Impossible de supprimer le(s) contact(s).';
$messages['contactdeleted'] = 'Contact(s) correctement supprimé(s).';
$messages['contactrestoreerror'] = 'Impossible de restaurer le(s) contact(s) supprimé(s).';
$messages['contactrestored'] = 'Contact(s) correctement restauré(s).';
$messages['groupdeleted'] = 'Le groupe a bien été supprimé.';
$messages['grouprenamed'] = 'Le groupe a bien été renommé.';
$messages['groupcreated'] = 'Le groupe a bien été créé.';
$messages['savedsearchdeleted'] = 'La recherche enregistrée a bien été supprimée.';
$messages['savedsearchdeleteerror'] = 'Impossible de supprimer la recherche enregistrée.';
$messages['savedsearchcreated'] = 'La recherche enregistrée a bien été créée.';
$messages['savedsearchcreateerror'] = 'Impossible de créer la recherche enregistrée.';
$messages['messagedeleted'] = 'Message(s) correctement supprimé(s).';
$messages['messagemoved'] = 'Message(s) correctement déplacé(s).';
$messages['messagecopied'] = 'Message(s) correctement copié(s).';
$messages['messagemarked'] = 'Message(s) correctement marqué(s).';
$messages['autocompletechars'] = 'Entrez au moins $min caractères pour l\'auto-complétion.';
$messages['autocompletemore'] = 'Plusieurs entrées trouvées. Veuillez taper plus de caractères.';
$messages['namecannotbeempty'] = 'Le nom ne peut pas être vide.';
$messages['nametoolong'] = 'Le nom est trop long.';
$messages['folderupdated'] = 'Le dossier a bien été mis à jour.';
$messages['foldercreated'] = 'Le dossier a bien été créé.';
$messages['invalidimageformat'] = 'Format d\'image invalide.';
$messages['mispellingsfound'] = 'Des fautes d\'orthographe ont été détectées dans le message.';
$messages['parentnotwritable'] = 'Impossible de créer/déplacer le dossier dans le dossier parent sélectionné. Aucun droit d\'accès.';
$messages['messagetoobig'] = 'Le message est trop gros pour être traité.';
$messages['attachmentvalidationerror'] = 'ATTENTION ! Cette pièce jointe est suspecte car son type ne correspond pas au type déclaré dans ce message. Si vous ne faites pas confiance à l\'expéditeur, vous ne devriez pas l\'ouvrir dans le navigateur, car il peut contenir des contenus malveillants.<br/><br/><em>Attendu : $expected; trouvé : $detected</em>';
$messages['noscriptwarning'] = 'Atention : Ce service de webmail nécessite Javascript ! Pour pouvoir l\'utiliser, merci d\'activer Javascript dans les préférences de votre navigateur.';

?>

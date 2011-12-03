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
$messages['sessionerror'] = 'Votre session est invalide ou a expirÃ©';
$messages['imaperror'] = 'Erreur de connexion au serveur IMAP';
$messages['servererror'] = 'Erreur Serveur !';
$messages['servererrormsg'] = 'Erreur du serveur: $msg';
$messages['dberror'] = 'Erreur avec la base de donnÃ©e!';
$messages['errorreadonly'] = 'Impossible d\'effectuer cette opÃ©ration. Le dossier est en lecture seule';
$messages['errornoperm'] = 'Impossible d\'effectuer cette opÃ©ration. Permission refusÃ©e';
$messages['invalidrequest'] = 'RequÃªte invalide ! Aucune donnÃ©e n\'a Ã©tÃ© sauvegardÃ©e.';
$messages['nomessagesfound'] = 'Aucun message trouvÃ© dans cette boÃ®te aux lettres';
$messages['loggedout'] = 'Vous venez de vous dÃ©connecter avec succÃšs. Au revoir !';
$messages['mailboxempty'] = 'BoÃ®te aux lettres vide';
$messages['loading'] = 'Chargement...';
$messages['uploading'] = 'Envoi du fichier...';
$messages['uploadingmany'] = 'Envoi des fichiers ...';
$messages['loadingdata'] = 'Chargement des donnÃ©es...';
$messages['checkingmail'] = 'VÃ©rification des nouveaux messages...';
$messages['sendingmessage'] = 'Message en cours d\'envoi...';
$messages['messagesent'] = 'Message bien envoyÃ©';
$messages['savingmessage'] = 'Sauvegarde du message...';
$messages['messagesaved'] = 'Message sauvegardÃ© dans Brouillons';
$messages['successfullysaved'] = 'Sauvegarde effectuÃ©e';
$messages['addedsuccessfully'] = 'Contact ajoutÃ© dans le carnet d\'adresses';
$messages['contactexists'] = 'Un contact avec ce courriel existe dÃ©jÃ ';
$messages['contactnameexists'] = 'Un contact avec le mÃªme nom existe dÃ©jÃ .';
$messages['blockedimages'] = 'Afin de prÃ©server votre vie privÃ©e, les images distantes ont Ã©tÃ© bloquÃ©es dans ce message.';
$messages['encryptedmessage'] = 'Ceci est un message cryptÃ© et il ne peut pas Ãªtre affichÃ©. DÃ©solÃ© !';
$messages['nocontactsfound'] = 'Aucun contact n\'a pu Ãªtre trouvÃ©';
$messages['contactnotfound'] = 'Le contact demandÃ© n\'a pas Ã©tÃ© trouvÃ©';
$messages['contactsearchonly'] = 'Entrez un ou plusieurs mots clÃ©s pour trouver des contacts';
$messages['sendingfailed'] = 'L\'envoi du message a Ã©chouÃ©';
$messages['senttooquickly'] = 'Vous devez attendre $sec sec. pour envoyer le message';
$messages['errorsavingsent'] = 'Une erreur est survenue pendant la sauvegarde du message envoyÃ©';
$messages['errorsaving'] = 'Une erreur a empÃªchÃ© la sauvegarde';
$messages['errormoving'] = 'Impossible de dÃ©placer le message';
$messages['errorcopying'] = 'Le(s) message(s) n\'a/ont pas pu Ãªtre copiÃ©(s)';
$messages['errordeleting'] = 'Impossible d\'effacer le message';
$messages['errormarking'] = 'Impossible de marquer le message';
$messages['deletecontactconfirm'] = 'Voulez-vous vraiment effacer le(s) contact(s) sÃ©lectionnÃ©(s) ?';
$messages['deletegroupconfirm'] = 'Ãtes-vous sÃ»r de vouloir supprimer le groupe sÃ©lectionnÃ© ?';
$messages['deletemessagesconfirm'] = 'Voulez-vous vraiment supprimer le(s) message(s) sÃ©lectionnÃ©(s) ?';
$messages['deletefolderconfirm'] = 'Voulez-vous vraiment effacer ce dossier ?';
$messages['purgefolderconfirm'] = 'Voulez-vous vraiment effacer tous les messages de ce dossier ?';
$messages['contactdeleting'] = 'Suppression de contact(s)...';
$messages['groupdeleting'] = 'Suppression du groupe ...';
$messages['folderdeleting'] = 'Suppression du dossier...';
$messages['foldermoving'] = 'DÃ©placement du dossier...';
$messages['foldersubscribing'] = 'Inscription du dossier...';
$messages['folderunsubscribing'] = 'DÃ©sinscription du dossier...';
$messages['formincomplete'] = 'Le formulaire n\'a pas Ã©tÃ© entiÃšrement rempli';
$messages['noemailwarning'] = 'Veuillez spÃ©cifier un courriel valide';
$messages['nonamewarning'] = 'Veuillez fournir un nom';
$messages['nopagesizewarning'] = 'Veuillez indiquer une taille de page';
$messages['nosenderwarning'] = 'Veuillez renseigner l\'adresse d\'expÃ©diteur';
$messages['norecipientwarning'] = 'Veuillez ajouter au moins un destinataire';
$messages['nosubjectwarning'] = 'Le champ Â« Objet Â» est vide. Souhaitez-vous le renseigner maintenant ?';
$messages['nobodywarning'] = 'Envoyer ce message sans texte ?';
$messages['notsentwarning'] = 'Le message n\'a pas Ã©tÃ© envoyÃ©. Voulez-vous abandonner ce message ?';
$messages['noldapserver'] = 'Choisissez un serveur LDAP pour la recherche';
$messages['nosearchname'] = 'Entrez un nom de contact ou un courriel';
$messages['notuploadedwarning'] = 'Toutes les piÃšces jointes ne sont pas encore envoyÃ©es pour le moment. Merci d\'attendre ou d\'annuler l\'envoi.';
$messages['searchsuccessful'] = '$nr messages trouvÃ©s';
$messages['contactsearchsuccessful'] = '$nr contact(s) trouvÃ©(s).';
$messages['searchnomatch'] = 'La recherche ne donne aucun rÃ©sultat';
$messages['searching'] = 'En cours de recherche...';
$messages['checking'] = 'VÃ©rification...';
$messages['nospellerrors'] = 'Aucune faute trouvÃ©e';
$messages['folderdeleted'] = 'Dossier effacÃ©';
$messages['foldersubscribed'] = 'Le dossier a bien Ã©tÃ© inscrit';
$messages['folderunsubscribed'] = 'Le dossier a bien Ã©tÃ© dÃ©sinscrit';
$messages['folderpurged'] = 'Le dossier a bien Ã©tÃ© vidÃ©';
$messages['folderexpunged'] = 'Le dossier a bien Ã©tÃ© compactÃ©';
$messages['deletedsuccessfully'] = 'SupprimÃ©(s) avec succÃšs';
$messages['converting'] = 'Suppression de la mise en forme...';
$messages['messageopenerror'] = 'Impossible de charger le message depuis serveur';
$messages['fileuploaderror'] = 'Transfert du fichier Ã©chouÃ©';
$messages['filesizeerror'] = 'Le fichier transfÃ©rÃ© dÃ©passe la taille maximale de $size';
$messages['copysuccess'] = 'SuccÃšs de la copie des $nr adresses';
$messages['copyerror'] = 'Ne peut pas copier les adresses';
$messages['sourceisreadonly'] = 'Cette source d\'adresse est en lecture seule';
$messages['errorsavingcontact'] = 'Ne peut pas enregistrer l\'adresse du contact';
$messages['movingmessage'] = 'DÃ©placement du message...';
$messages['copyingmessage'] = 'Copie du message ...';
$messages['copyingcontact'] = 'Copie des contacts ...';
$messages['deletingmessage'] = 'Suppression des messages...';
$messages['markingmessage'] = 'Marquage des messages...';
$messages['addingmember'] = 'Ajout des contacts dans le groupe ...';
$messages['removingmember'] = 'Suppression des contacts du groupe ...';
$messages['receiptsent'] = 'L\'accusÃ© de rÃ©ception a bien Ã©tÃ© envoyÃ©';
$messages['errorsendingreceipt'] = 'L\'accusÃ© de rÃ©ception n\'a pas pu Ãªtre envoyÃ©';
$messages['nodeletelastidentity'] = 'Vous ne pouvez pas effacer votre seule identitÃ©.';
$messages['forbiddencharacter'] = 'Le nom du dossier contient un caractÃšre interdit';
$messages['selectimportfile'] = 'Veuillez sÃ©lectionner un fichier Ã  envoyer';
$messages['addresswriterror'] = 'Impossible d\'Ã©crire dans le carnet d\'adresse sÃ©lectionnÃ©';
$messages['contactaddedtogroup'] = 'Les contacts ont bien Ã©tÃ© ajoutÃ©s Ã  ce groupe';
$messages['contactremovedfromgroup'] = 'Les contacts ont bien Ã©tÃ© supprimÃ©s de ce groupe';
$messages['importwait'] = 'Importation, veuillez patienter...';
$messages['importerror'] = 'Importation Ã©chouÃ©e ! Le fichier envoyÃ© n\'est pas un fichier vCard valide.';
$messages['importconfirm'] = '<b>$inserted contacts importÃ©s avec succÃšs, $skipped entrÃ©es existantes ignorÃ©es</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>$skipped entrÃ©e(s) dÃ©jÃ  existante(s)</b>';
$messages['opnotpermitted'] = 'Cette opÃ©ration n\'est pas permise !';
$messages['nofromaddress'] = 'Il manque une adresse e-mail dans l\'identitÃ© sÃ©lectionnÃ©e';
$messages['editorwarning'] = 'Passer Ã  l\'Ã©diteur texte seul causera la perte du formatage du texte. Voulez-vous continuer ?';
$messages['httpreceivedencrypterror'] = 'Une erreur fatale de configuration est survenue. Veuillez contacter votre administrateur immÃ©diatement. <b>Votre message n\'a pas pu Ãªtre envoyÃ©.</b>';
$messages['smtpconnerror'] = 'Erreur SMTP ($code): Echec de la connexion au serveur';
$messages['smtpautherror'] = 'Erreur SMTP ($code): Echec de l\'authentification';
$messages['smtpfromerror'] = 'Erreur SMTP ($code): Impossible de dÃ©finir l\'expÃ©diteur "$from" ($msg)';
$messages['smtptoerror'] = 'Erreur SMTP ($code): Impossible d\'ajouter le destinataire "$to" ($msg)';
$messages['smtprecipientserror'] = 'Erreur SMTP: Impossible de lire la liste des destinataires';
$messages['smtperror'] = 'Erreur SMTP: $msg';
$messages['emailformaterror'] = 'Adresse email incorrecte: $email';
$messages['toomanyrecipients'] = 'Trop de destinataires. RÃ©duisez leur nombre Ã  $max maximum.';
$messages['maxgroupmembersreached'] = 'Le nombre de membres du groupe dÃ©passe le maximum de $max.';
$messages['internalerror'] = 'Une erreur interne est survenue. Merci de rÃ©essayer';
$messages['contactdelerror'] = 'Les contacts n\'ont pas pu Ãªtre supprimÃ©s';
$messages['contactdeleted'] = 'Les contacts ont bien Ã©tÃ© supprimÃ©s';
$messages['contactrestoreerror'] = 'Impossible de restaurer les contacts supprimÃ©s.';
$messages['contactrestored'] = 'Les contacts ont bien Ã©tÃ© restaurÃ©s.';
$messages['groupdeleted'] = 'Le groupe a bien Ã©tÃ© supprimÃ©';
$messages['grouprenamed'] = 'Le groupe a bien Ã©tÃ© renommÃ©';
$messages['groupcreated'] = 'Le groupe a bien Ã©tÃ© crÃ©Ã©';
$messages['savedsearchdeleted'] = 'La recherche enregistrÃ©e a bien Ã©tÃ© supprimÃ©e.';
$messages['savedsearchdeleteerror'] = 'Impossible de supprimer la recherche enregistrÃ©e.';
$messages['savedsearchcreated'] = 'La recherche enregistrÃ©e a bien Ã©tÃ© crÃ©Ã©e.';
$messages['savedsearchcreateerror'] = 'Impossible de crÃ©er la recherche enregistrÃ©e.';
$messages['messagedeleted'] = 'Les messages ont bien Ã©tÃ© supprimÃ©s';
$messages['messagemoved'] = 'Les messages ont bien Ã©tÃ© dÃ©placÃ©s';
$messages['messagecopied'] = 'Les messages ont bien Ã©tÃ© copiÃ©s';
$messages['messagemarked'] = 'Les messages ont bien Ã©tÃ© marquÃ©s';
$messages['autocompletechars'] = 'Entrez au moins $min caractÃšres pour l\'auto-complÃ©tion';
$messages['autocompletemore'] = 'Plusieurs entrÃ©es trouvÃ©es. Tapez plus de caractÃšres.';
$messages['namecannotbeempty'] = 'Le nom ne peut pas Ãªtre vide';
$messages['nametoolong'] = 'Le nom est trop long';
$messages['folderupdated'] = 'Le dossier a bien Ã©tÃ© mis Ã  jour';
$messages['foldercreated'] = 'Le dossier a bien Ã©tÃ© crÃ©Ã©';
$messages['invalidimageformat'] = 'Format d\'image invalide';
$messages['mispellingsfound'] = 'Des fautes d\'orthographe ont Ã©tÃ© dÃ©tectÃ©es dans le message.';
$messages['parentnotwritable'] = 'Impossible de crÃ©er/dÃ©placer le dossier dans le dossier parent sÃ©lectionnÃ©. Aucun droit d\'accÃšs.';
$messages['messagetoobig'] = 'Le message est trop gros pour Ãªtre traitÃ©.';

?>
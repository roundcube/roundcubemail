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
$messages['errortitle']  = 'Έχει προκύψει κάποιο σφάλμα!';
$messages['loginfailed']  = 'Είσοδος απέτυχε';
$messages['cookiesdisabled'] = 'Ο περιηγητής σας (browser) δεν αποδέχεται cookies';
$messages['sessionerror'] = 'Η συνεδρία σας είναι άκυρη ή έχει λήξει';
$messages['storageerror'] = 'Η σύνδεση με το διακομιστή IMAP απέτυχε';
$messages['servererror'] = 'Σφάλμα του εξυπηρετητή!';
$messages['servererrormsg'] = 'Σφάλμα εξυπηρετητή: $msg';
$messages['dberror'] = 'Σφάλμα βάσης δεδομένων!';
$messages['requesttimedout'] = 'Το αίτημα εξέπνευσε χρονικά';
$messages['errorreadonly'] = 'Η διαδικασία δεν μπορεί να ολοκληρωθεί. Ο φάκελος είναι μόνο για ανάγνωση.';
$messages['errornoperm'] = 'Η διαδικασία δεν μπορεί να ολοκληρωθεί. Άρνηση άδειας';
$messages['invalidrequest'] = 'Λανθασμένο αίτημα! Δεν αποθηκεύτηκαν δεδομένα.';
$messages['invalidhost'] = 'Άκυρο όνομα εξυπηρετητή.';
$messages['nomessagesfound'] = 'Δε βρέθηκαν μηνύματα σε αυτή τη θυρίδα';
$messages['loggedout'] = 'Έχετε τερματίσει επιτυχώς τη συνεδρία. Αντίο!';
$messages['mailboxempty'] = 'Η θυρίδα είναι άδεια';
$messages['refreshing'] = 'Ανανέωση....';
$messages['loading'] = 'Φόρτωση...';
$messages['uploading'] = 'Το αρχείο φορτώνεται...';
$messages['uploadingmany'] = 'Φόρτωση αρχείων...';
$messages['loadingdata'] = 'Φόρτωση δεδομένων...';
$messages['checkingmail'] = 'Έλεγχος για νέα μηνύματα...';
$messages['sendingmessage'] = 'Αποστολή μηνύματος...';
$messages['messagesent'] = 'Μήνυμα εστάλη επιτυχώς';
$messages['savingmessage'] = 'Αποθήκευση μηνύματος...';
$messages['messagesaved'] = 'Μήνυμα αποθηκεύτηκε στα Πρόχειρα';
$messages['successfullysaved'] = 'Αποθηκεύτηκε επιτυχώς';
$messages['addedsuccessfully'] = 'Η επαφή προστέθηκε επιτυχώς στις Επαφές';
$messages['contactexists'] = 'Υπάρχει ήδη επαφή με αυτή τη διεύθυνση e-mail';
$messages['contactnameexists'] = 'Μια επαφή με το ίδιο όνομα υπάρχει ήδη.';
$messages['blockedimages'] = 'Για να προστατευτεί το απόρρητο σας, οι απομακρυσμένες εικόνες έχουν απενεργοποιηθεί σε αυτό το μήνυμα.';
$messages['encryptedmessage'] = 'Αυτό το μήνυμα είναι κρυπτογραφημένο και δε μπορεί να προβληθεί. Συγνώμη!';
$messages['nocontactsfound'] = 'Δε βρέθηκαν επαφές';
$messages['contactnotfound'] = 'Η ζητούμενη επαφή δεν βρέθηκε';
$messages['contactsearchonly'] = 'Εισάγετε κάποιους όρους προς αναζήτηση';
$messages['sendingfailed'] = 'Αποστολή μηνύματος απέτυχε';
$messages['senttooquickly'] = 'Παρακαλώ περιμένετε $sec δευτερόλεπτα, πριν στείλετε το μήνυμα';
$messages['errorsavingsent'] = 'Παρουσιάστηκε σφάλμα, κατά την αποθήκευση του απεσταλμένου μηνύματος';
$messages['errorsaving'] = 'Παρουσιάστηκε σφάλμα κατά την αποθήκευση';
$messages['errormoving'] = 'Το μήνυμα δε μπορούσε να μετακινηθεί';
$messages['errorcopying'] = 'Δεν είναι δυνατή η αντιγραφή του μηνύματος/των';
$messages['errordeleting'] = 'Το μήνυμα δε μπορούσε να διαγραφεί';
$messages['errormarking'] = 'Δεν έγινε επισήμανση στο μύνημα';
$messages['deletecontactconfirm']  = 'Θέλετε να διαγράψετε τη συγκεκριμένη επαφή/ές;';
$messages['deletegroupconfirm']  = 'Είστε βέβαιοι πως θέλετε να διαγράψετε την επιλεγμένη ομάδα;';
$messages['deletemessagesconfirm'] = 'Θέλετε να διαγράψετε το συγκεκριμένο μήνυμα/τα;';
$messages['deletefolderconfirm']  = 'Θέλετε να διαγράψετε το συγκεκριμένο φάκελο;';
$messages['purgefolderconfirm']  = 'Θέλετε να διαγράψετε όλα τα μηνύματα στο συγκεκριμένο φάκελο;';
$messages['contactdeleting'] = 'Διαγραφή επαφών...';
$messages['groupdeleting'] = 'Διαγραφή ομάδας...';
$messages['folderdeleting'] = 'Διαγραφή φακέλου';
$messages['foldermoving'] = 'Μεταφορά φακέλου';
$messages['foldersubscribing'] = 'Εγγραφή φακέλου...';
$messages['folderunsubscribing'] = 'Απεγγραφή φακέλου...';
$messages['formincomplete'] = 'Η φόρμα δεν έχει συμπληρωθεί πλήρως';
$messages['noemailwarning'] = 'Παρακαλώ εισάγετε έγκυρη διεύθυνση email';
$messages['nonamewarning']  = 'Παρακαλώ εισάγετε όνομα';
$messages['nopagesizewarning'] = 'Παρακαλώ εισάγετε μέγεθος σελίδας';
$messages['nosenderwarning'] = 'Παρακαλώ εισάγετε την email διεύθυνση αποστολέα';
$messages['norecipientwarning'] = 'Παρακαλώ εισάγεται τουλάχιστον έναν παραλήπτη';
$messages['nosubjectwarning']  = 'Το πεδίο "Θέμα" είναι άδειο. Θέλετε να εισάγεται ένα τώρα;';
$messages['nobodywarning'] = 'Αποστολή μηνύματος χωρίς κείμενο;';
$messages['notsentwarning'] = 'Το μήνυμα δεν έχει σταλεί. Θέλετε να το απορρίψετε;';
$messages['noldapserver'] = 'Παρακαλώ επιλέξτε έναν LDAP διακομιστή για αναζήτηση';
$messages['nosearchname'] = 'Παρακαλώ εισάγεται όνομα επαφής ή διεύθυνση email';
$messages['notuploadedwarning'] = 'Δεν έχουν φορτωθεί όλα τα συνημμένα. Παρακαλώ περιμένετε ή ακυρώστε το ανέβασμα.';
$messages['searchsuccessful'] = '$nr μηνύματα βρέθηκαν';
$messages['contactsearchsuccessful'] = '$nr επαφές βρέθηκαν.';
$messages['searchnomatch'] = 'Η αναζήτηση δεν επέστρεψε αποτελέσματα';
$messages['searching'] = 'Αναζήτηση...';
$messages['checking'] = 'Έλεγχος...';
$messages['nospellerrors'] = 'Δε βρέθηκαν ορθογραφικά λάθη';
$messages['folderdeleted'] = 'Ο φάκελος διαγράφηκε επιτυχώς';
$messages['foldersubscribed'] = 'Folder successfully subscribed.';
$messages['folderunsubscribed'] = 'Folder successfully unsubscribed.';
$messages['folderpurged'] = 'Ο φάκελος καθαρίστηκε με επιτυχία.';
$messages['folderexpunged'] = 'Ο φάκελος συμπιέστηκε με επιτυχία.';
$messages['deletedsuccessfully'] = 'Διαγραφή Επιτυχώς';
$messages['converting'] = 'Μετακίνηση διαμορφώσεων κειμένου...';
$messages['messageopenerror'] = 'Η φόρτωση μηνυμάτων από τον διακομιστή απέτυχε';
$messages['fileuploaderror'] = 'Ανέβασμα αρχείου απέτυχε';
$messages['filesizeerror'] = 'Το ανεβασμένο αρχείο ξεπερνάει το μέγιστο μέγεθος των $size';
$messages['copysuccess'] = 'Επιτυχής αντιγραφή $nr διευθύνσεων';
$messages['copyerror'] = 'Αποτυχία αντιγραφής διευθύνσεων';
$messages['sourceisreadonly'] = 'Η διεύθυνση έχει μόνο δικαίωμα ανάγνωσης';
$messages['errorsavingcontact'] = 'Αποθήκευση επαφής απέτυχε';
$messages['movingmessage'] = 'Μετακίνηση μηνύματος...';
$messages['copyingmessage'] = 'Αντιγραφή μηνυμάτων...';
$messages['copyingcontact'] = 'Αντιγραφή επαφών...';
$messages['deletingmessage'] = 'Διαγραφή μηνυμάτων...';
$messages['markingmessage'] = 'Σήμανση μηνυμάτων...';
$messages['addingmember'] = 'Προσθήκη επαφών στην ομάδα...';
$messages['removingmember'] = 'Αφαίρεση επαφών από την ομάδα...';
$messages['receiptsent'] = 'Επιτυχής αποστολή αναφοράς ανάγνωσης';
$messages['errorsendingreceipt'] = 'Αποστολή αναφοράς απέτυχε';
$messages['deleteidentityconfirm'] = 'Είστε βέβαιοι πως θέλετε να διαγράψετε την ταυτότητα;';
$messages['nodeletelastidentity'] = 'Δε μπορείτε να διαγράψετε αυτήν την ταυτότητα, είναι η τελευταία.';
$messages['forbiddencharacter'] = 'Η ονομασία φακέλου περιέχει μη επιτρεπτό χαρακτήρα';
$messages['selectimportfile'] = 'Επιλέξτε αρχείο για ανέβασμα';
$messages['addresswriterror'] = 'Η επιλεγμένη ατζέντα διευθύνσεων δεν είνα εγγράψιμη';
$messages['contactaddedtogroup'] = 'Οι επαφές προστέθηκαν στην ομάδα με επιτυχία.';
$messages['contactremovedfromgroup'] = 'Οι επαφές αφαιρέθηκαν από την ομάδα με επιτυχία.';
$messages['nogroupassignmentschanged'] = 'No group assignments changed.';
$messages['importwait'] = 'Εισαγωγή, παρακαλώ περιμένετε...';
$messages['importformaterror'] = 'Import failed! The uploaded file is not a valid import data file.';
$messages['importconfirm'] = '<b>Εισήχθησαν $inserted επαφές με επιτυχία</b>';
$messages['importconfirmskipped'] = '<b>Παραλήφθηκαν $skipped υπάρχοντα αντικείμενα</b>';
$messages['opnotpermitted'] = 'Η λειτουργία δεν επιτρέπεται!';
$messages['nofromaddress'] = 'Στην επιλεγμένη ταυτότητα, λείπει η email διεύθυνση';
$messages['editorwarning'] = 'Η μετάβαση στην επεξεργασία απλού κειμένου θα προκαλέσει κατάργηση της μορφοποίησης. Είστε βέβαιοι πως θέλετε να συνεχίσετε;';
$messages['httpreceivedencrypterror'] = 'Ούπς! Ανεπανόθωτο σφάλμα. Επικοινωνήστε με το διαχειριστή του εξυπηρετητή. <b>Το μηνυμά σας δεν στάλθηκε.</b>';
$messages['smtpconnerror'] = 'Σφάλμα SMTP ($code): Απέτυχε η σύνδεση με τον εξυπηρετητή';
$messages['smtpautherror'] = 'Σφάλμα SMTP ($code): Σφάλμα ταυτοποίησης';
$messages['smtpfromerror'] = 'Σφάλμα SMTP ($code): Αποτυχία ορισμού αποστολέα "$from" ($msg)';
$messages['smtptoerror'] = 'Σφάλμα SMTP ($code): Αποτυχία προσθήκης παραλήπτη "$to" ($msg)';
$messages['smtprecipientserror'] = 'Σφάλμα SMTP: Αδύνατο να προσπελαστεί η λίστα παραληπτών';
$messages['smtperror'] = 'Σφάλμα SMTP: $msg';
$messages['emailformaterror'] = 'Λανθασμένη διεύθνση email: $email';
$messages['toomanyrecipients'] = 'Μεγάλο πλήθος αποδεκτών. Μειώστε τον αριθμό των αποδεκτών σε $max.';
$messages['maxgroupmembersreached'] = 'Ο αριθμός των μελών της ομάδας υπερβαίνει τον μέγιστο των $max μελών.';
$messages['internalerror'] = 'Προέκυψε ένα εσωτερικό σφάλμα. Παρακαλώ δοκιμάστε πάλι.';
$messages['contactdelerror'] = 'Δεν είναι δυνατή η διαγραφή της επαφής/ων';
$messages['contactdeleted'] = 'Η επαφή/ές διαγράφηκαν με επιτυχία.';
$messages['contactrestoreerror'] = 'Could not restore deleted contact(s).';
$messages['contactrestored'] = 'Contact(s) restored successfully.';
$messages['groupdeleted'] = 'Group deleted successfully.';
$messages['grouprenamed'] = 'Group renamed successfully.';
$messages['groupcreated'] = 'Group created successfully.';
$messages['savedsearchdeleted'] = 'Saved search deleted successfully.';
$messages['savedsearchdeleteerror'] = 'Could not delete saved search.';
$messages['savedsearchcreated'] = 'Saved search created successfully.';
$messages['savedsearchcreateerror'] = 'Could not create saved search.';
$messages['messagedeleted'] = 'Message(s) deleted successfully.';
$messages['messagemoved'] = 'Message(s) moved successfully.';
$messages['messagecopied'] = 'Message(s) copied successfully.';
$messages['messagemarked'] = 'Message(s) marked successfully.';
$messages['autocompletechars'] = 'Enter at least $min characters for autocompletion.';
$messages['autocompletemore'] = 'More matching entries found. Please type more characters.';
$messages['namecannotbeempty'] = 'Name cannot be empty.';
$messages['nametoolong'] = 'Name is too long.';
$messages['folderupdated'] = 'Folder updated successfully.';
$messages['foldercreated'] = 'Folder created successfully.';
$messages['invalidimageformat'] = 'Not a valid image format.';
$messages['mispellingsfound'] = 'Spelling errors detected in the message.';
$messages['parentnotwritable'] = 'Unable to create/move folder into selected parent folder. No access rights.';
$messages['messagetoobig'] = 'The message part is too big to process it.';
$messages['attachmentvalidationerror'] = 'WARNING! This attachment is suspicious because its type doesn\'t match the type declared in the message. If you do not trust the sender, you shouldn\'t open it in the browser because it may contain malicious contents.<br/><br/><em>Expected: $expected; found: $detected</em>';
$messages['noscriptwarning'] = 'Warning: This webmail service requires Javascript! In order to use it please enable Javascript in your browser\'s settings.';

?>

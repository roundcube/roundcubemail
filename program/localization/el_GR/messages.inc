<?php
/*
+-----------------------------------------------------------------------+
| language/el/messages.inc                                              |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Jim Deves <jim@cobaltblue.gr>                                 |
|         John Economou <hsoc@irc.gr>                                   |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Είσοδος απέτυχε';
$messages['cookiesdisabled'] = 'Ο περιηγητής σας (browser) δεν αποδέχεται cookies';
$messages['sessionerror'] = 'Η συνεδρία σας είναι άκυρη ή έχει λήξει';
$messages['imaperror'] = 'Η σύνδεση με το διακομιστή IMAP απέτυχε';
$messages['servererror'] = 'Σφάλμα του εξυπηρετητή!';
$messages['invalidrequest'] = 'Λανθασμένο αίτημα! Δεν αποθηκεύτηκαν δεδομένα.';
$messages['nomessagesfound'] = 'Δε βρέθηκαν μηνύματα σε αυτή τη θυρίδα';
$messages['loggedout'] = 'Έχετε τερματίσει επιτυχώς τη συνεδρία. Αντίο!';
$messages['mailboxempty'] = 'Η θυρίδα είναι άδεια';
$messages['loading'] = 'Φόρτωση...';
$messages['uploading'] = 'Το αρχείο φορτώνεται...';
$messages['loadingdata'] = 'Φόρτωση δεδομένων...';
$messages['checkingmail'] = 'Έλεγχος για νέα μηνύματα...';
$messages['sendingmessage'] = 'Αποστολή μηνύματος...';
$messages['messagesent'] = 'Μήνυμα εστάλη επιτυχώς';
$messages['savingmessage'] = 'Αποθήκευση μηνύματος...';
$messages['messagesaved'] = 'Μήνυμα αποθηκεύτηκε στα Πρόχειρα';
$messages['successfullysaved'] = 'Αποθηκεύτηκε επιτυχώς';
$messages['addedsuccessfully'] = 'Η επαφή προστέθηκε επιτυχώς στο Βιβλίο Διευθύνσεων';
$messages['contactexists'] = 'Υπάρχει ήδη επαφή με αυτή τη διεύθυνση e-mail';
$messages['blockedimages'] = 'Για να προστατευτεί το απόρρητο σας, οι απομακρυσμένες εικόνες έχουν απενεργοποιηθεί σε αυτό το μήνυμα.';
$messages['encryptedmessage'] = 'Αυτό το μήνυμα είναι κρυπτογραφημένο και δε μπορεί να προβληθεί. Συγνώμη!';
$messages['nocontactsfound'] = 'Δε βρέθηκαν επαφές';
$messages['contactnotfound'] = 'Η ζητούμενη επαφή δεν βρέθηκε';
$messages['sendingfailed'] = 'Αποστολή μηνύματος απέτυχε';
$messages['senttooquickly'] = 'Παρακαλώ περιμένετε $sec δευτερόλεπτα, πριν στείλετε το μήνυμα';
$messages['errorsavingsent'] = 'Παρουσιάστηκε σφάλμα, κατά την αποθήκευση του απεσταλμένου μηνύματος';
$messages['errorsaving'] = 'Παρουσιάστηκε σφάλμα κατά την αποθήκευση';
$messages['errormoving'] = 'Το μήνυμα δε μπορούσε να μετακινηθεί';
$messages['errordeleting'] = 'Το μήνυμα δε μπορούσε να διαγραφεί';
$messages['errormarking'] = 'Δεν έγινε επισήμανση στο μύνημα';
$messages['deletecontactconfirm'] = 'Θέλετε να διαγράψετε τη συγκεκριμένη επαφή/ές;';
$messages['deletemessagesconfirm'] = 'Θέλετε να διαγράψετε το συγκεκριμένο μήνυμα/τα;';
$messages['deletefolderconfirm'] = 'Θέλετε να διαγράψετε το συγκεκριμένο φάκελο;';
$messages['purgefolderconfirm'] = 'Θέλετε να διαγράψετε όλα τα μηνύματα στο συγκεκριμένο φάκελο;';
$messages['foldercreating'] = 'Δημιουργία φακέλου';
$messages['folderdeleting'] = 'Διαγραφή φακέλου';
$messages['folderrenaming'] = 'Μετονομασία φακέλου';
$messages['foldermoving'] = 'Μεταφορά φακέλου';
$messages['formincomplete'] = 'Η φόρμα δεν έχει συμπληρωθεί πλήρως';
$messages['noemailwarning'] = 'Παρακαλώ εισάγετε έγκυρη διεύθυνση email';
$messages['nonamewarning'] = 'Παρακαλώ εισάγετε όνομα';
$messages['nopagesizewarning'] = 'Παρακαλώ εισάγετε μέγεθος σελίδας';
$messages['nosenderwarning'] = 'Παρακαλώ εισάγετε την email διεύθυνση αποστολέα';
$messages['norecipientwarning'] = 'Παρακαλώ εισάγεται τουλάχιστον έναν παραλήπτη';
$messages['nosubjectwarning'] = 'Το πεδίο "Θέμα" είναι άδειο. Θέλετε να εισάγεται ένα τώρα;';
$messages['nobodywarning'] = 'Αποστολή μηνύματος χωρίς κείμενο;';
$messages['notsentwarning'] = 'Το μήνυμα δεν έχει σταλεί. Θέλετε να το απορρίψετε;';
$messages['noldapserver'] = 'Παρακαλώ επιλέξτε έναν LDAP διακομιστή για αναζήτηση';
$messages['nocontactsreturned'] = 'Δε βρέθηκαν επαφές';
$messages['nosearchname'] = 'Παρακαλώ εισάγεται όνομα επαφής ή διεύθυνση email';
$messages['notuploadedwarning'] = 'Δεν έχουν φορτωθεί όλα τα συνημένα. Παρακαλώ περιμένετε ή ακυρώστε το ανέβασμα.';
$messages['searchsuccessful'] = '$nr μηνύματα βρέθηκαν';
$messages['searchnomatch'] = 'Η αναζήτηση δεν επέστρεψε αποτελέσματα';
$messages['searching'] = 'Αναζήτηση...';
$messages['checking'] = 'Έλεγχος...';
$messages['nospellerrors'] = 'Δε βρέθηκαν ορθογραφικά λάθη';
$messages['folderdeleted'] = 'Ο φάκελος διαγράφηκε επιτυχώς';
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
$messages['receiptsent'] = 'Επιτυχής αποστολή αναφοράς ανάγνωσης';
$messages['errorsendingreceipt'] = 'Αποστολή αναφοράς απέτυχε';
$messages['nodeletelastidentity'] = 'Δε μπορείτε να διαγράψετε αυτήν την ταυτότητα, είναι η τελευταία.';
$messages['addsubfolderhint'] = 'Αυτός ο φάκελος θα δημιουργηθεί ως υποφάκελος του επιλεγμένου';
$messages['forbiddencharacter'] = 'Η ονομασία φακέλου περιέχει μη επιτρεπτό χαρακτήρα';
$messages['selectimportfile'] = 'Επιλέξτε αρχείο για ανέβασμα';
$messages['addresswriterror'] = 'Η επιλεγμένη ατζέντα διευθύνσεων δεν είνα εγγράψιμη';
$messages['importwait'] = 'Εισαγωγή, παρακαλώ περιμένετε...';
$messages['importerror'] = 'Η εισαγωγή απέτυχε! Το αρχείο δεν είναι έγκυρο vcard αρχείο.';
$messages['opnotpermitted'] = 'Η λειτουργία δεν επιτρέπεται!';
$messages['nofromaddress'] = 'Στην επιλεγμένη ταυτότητα, λείπει η email διεύθυνση';
$messages['httpreceivedencrypterror'] = 'Ούπς! Ανεπανόθωτο σφάλμα. Επικοινωνήστε με το διαχειριστή του εξυπηρετητή. <b>Το μηνυμά σας δεν στάλθηκε.</b>';
$messages['smtpconnerror'] = 'Σφάλμα SMTP ($code): Απέτυχε η σύνδεση με τον εξυπηρετητή';
$messages['smtpautherror'] = 'Σφάλμα SMTP ($code): Σφάλμα ταυτοποίησης';
$messages['smtpfromerror'] = 'Σφάλμα SMTP ($code): Αποτυχία ορισμού αποστολέα "$from"';
$messages['smtptoerror'] = 'Σφάλμα SMTP ($code): Αποτυχία προσθήκης παραλήπτη "$to"';
$messages['smtprecipientserror'] = 'Σφάλμα SMTP: Αδύνατο να προσπελαστεί η λίστα παραληπτών';
$messages['smtperror'] = 'Σφάλμα SMTP: $msg';
$messages['emailformaterror'] = 'Λανθασμένη διεύθνση email: $email';

?>

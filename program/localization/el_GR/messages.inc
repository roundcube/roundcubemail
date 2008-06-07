<?php

/*

 +-----------------------------------------------------------------------+
 | language/el/messages.inc                                              |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Jim Deves <jim@cobaltblue.gr>                                 |
 |         John Economou <hsoc@irc.gr>                                   |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Είσοδος Απέτυχε';
$messages['cookiesdisabled'] = 'Ο περιηγητής σας (browser) δεν αποδέχεται cookies';
$messages['sessionerror'] = 'Η συνεδρία σας είναι άκυρη ή έχει λήξη';
$messages['imaperror'] = 'Η σύνδεση με το διακομιστή IMAP απέτυχε';
$messages['nomessagesfound'] = 'Δε βρέθηκαν μηνύματα σε αυτή τη θυρίδα';
$messages['loggedout'] = 'Έχετε τερματίσει επιτυχώς τη συνεδρία. Αντίο!';
$messages['mailboxempty'] = 'Η θυρίδα είναι άδεια';
$messages['loading'] = 'Φόρτωση...';
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
$messages['errorsaving'] = 'Παρουσιάστηκε σφάλμα κατά την αποθήκευση';
$messages['errormoving'] = 'Το μήνυμα δε μπορούσε να μετακινηθεί';
$messages['errordeleting'] = 'Το μήνυμα δε μπορούσε να διαγραφεί';
$messages['deletecontactconfirm'] = 'Θέλετε να διαγράψετε τη συγκεκριμένη επαφή/ές;';
$messages['deletemessagesconfirm'] = 'Θέλετε να διαγράψετε το συγκεκριμένο μήνυμα/τα;';
$messages['deletefolderconfirm'] = 'Θέλετε να διαγράψετε το συγκεκριμένο φάκελο;';
$messages['purgefolderconfirm'] = 'Θέλετε να διαγράψετε όλα τα μηνύματα στο συγκεκριμένο φάκελο;';
$messages['formincomplete'] = 'Η φόρμα δεν έχει συμπληρωθεί πλήρως';
$messages['noemailwarning'] = 'Παρακαλώ εισάγεται έγκυρη διεύθυνση email';
$messages['nonamewarning'] = 'Παρακαλώ εισάγεται όνομα';
$messages['nopagesizewarning'] = 'Παρακαλώ εισάγεται μέγεθος σελίδας';
$messages['norecipientwarning'] = 'Παρακαλώ εισάγεται τουλάχιστον έναν παραλήπτη';
$messages['nosubjectwarning'] = 'Το πεδίο "Θέμα" είναι άδειο. Θέλετε να εισάγεται ένα τώρα;';
$messages['nobodywarning'] = 'Αποστολή μηνύματος χωρίς κείμενο;';
$messages['notsentwarning'] = 'Το μήνυμα δεν έχει σταλεί. Θέλετε να το απορρίψετε;';
$messages['noldapserver'] = 'Παρακαλώ επιλέξτε έναν LDAP διακομιστή για αναζήτηση';
$messages['nocontactsreturned'] = 'Δε βρέθηκαν επαφές';
$messages['nosearchname'] = 'Παρακαλώ εισάγεται όνομα επαφής ή διεύθυνση email';
$messages['searchsuccessful'] = '$nr μηνύματα βρέθηκαν';
$messages['searchnomatch'] = 'Η Εύρεση δεν επέστρεψε αποτελέσματα';
$messages['searching'] = 'Εύρεση...';
$messages['checking'] = 'Έλεγχος...';
$messages['nospellerrors'] = 'Δε βρέθηκαν ορθογραφικά λάθη';
$messages['folderdeleted'] = 'Ο φάκελος διαγράφηκε επιτυχώς';
$messages['deletedsuccessfully'] = 'Διαγραφή Επιτυχώς';
$messages['converting'] = 'Μετακίνηση διαμορφώσεων κειμένου...';
$messages['messageopenerror'] = 'Φόρτωση μηνυμάτων από τον διακομιστή απέτυχε';
$messages['fileuploaderror'] = 'Ανέβασμα αρχείου απέτυχε';
$messages['filesizeerror'] = 'Το ανεβασμένο αρχείο ξεπερνάει το μέγιστο μέγεθος των $size';
$messages['copysuccess'] = 'Επιτυχή αντιγραφή $nr διευθύνσεων';
$messages['copyerror'] = 'Αποτυχία αντιγραφής διευθύνσεων';
$messages['sourceisreadonly'] = 'Η διεύθυνση έχει μόνο δικαίωμα ανάγνωσης';
$messages['errorsavingcontact'] = 'Σώσιμο επαφής απέτυχε';
$messages['movingmessage'] = 'Μετακίνηση μηνύματος...';
$messages['receiptsent'] = 'Επιτυχής αποστολή αναφοράς ανάγνωσης';
$messages['errorsendingreceipt'] = 'Αποστολή αναφοράς απέτυχε';
$messages['nodeletelastidentity'] = 'Δε μπορείτε να διαγράψετε αυτήν την ταυτότητα, είναι η τελευταία.';
$messages['addsubfolderhint'] = 'Αυτός ο φάκελος θα δημιουργηθεί ως υποφάκελος του επιλεγμένου';

?>
<?php

/*
 +------------------------------------------------------------------------------+
 | language/el/labels.inc                                                       |
 |                                                                              |
 | Language file of the RoundCube Webmail client                                |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                             |
 | Licensed under the GNU GPL                                                   |
 |                                                                              |
 +------------------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                               |
 +------------------------------------------------------------------------------+
 | Greek Translation: Panormitis Petrou <panormitis@gmx.net>                    |
 +------------------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Welcome to Roundcube|Mail';
$labels['username']  = 'Όνομα χρήστη';
$labels['password']  = 'Κωδικός πρόσβασης';
$labels['server']    = 'Διακομιστής';
$labels['login']     = 'Είσοδος';

// taskbar
$labels['logout']   = 'Αποσύνδεση';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Προσωπικές ρυθμίσεις';
$labels['addressbook'] = 'Βιβλίο διευθύνσεων';

// mailbox names
$labels['inbox']  = 'Εισερχόμενα';
$labels['sent']   = 'Απεσταλμένα';
$labels['trash']  = 'Κάδος αχρήστων';
$labels['drafts'] = 'Προσχέδια';
$labels['junk']   = '’χρηστα';

// message listing
$labels['subject'] = 'Θέμα';
$labels['nosubject']  = '(χωρίς θέμα)';
$labels['from']    = 'Αποστολέας';
$labels['to']      = 'Παραλήπτης';
$labels['cc']      = 'Κοινοποίηση';
$labels['bcc']     = 'Κρυφή κοινοποίηση';
$labels['replyto'] = 'Απάντηση προς';
$labels['replytoallmessage'] = 'Απάντηση προς τον αποστολέα και προς όλους τους παραλήπτες';
$labels['date']    = 'Ημερομηνία';
$labels['size']    = 'Μέγεθος';
$labels['priority'] = 'Προτεραιότητα';
$labels['organization'] = 'Οργανισμός';
$labels['sortby']   = 'Ταξινόμηση κατά';
$labels['sortasc']  = 'Αύξουσα ταξινόμηση';
$labels['sortdesc'] = 'Φθίνουσα ταξινόμηση';

// aliases
$labels['reply-to'] = $labels['Απάντηση προς'];

$labels['mailboxlist'] = 'Φάκελοι';
$labels['messagesfromto'] = 'Μηνύματα $from έως $to από $count';
$labels['messagenrof'] = 'Μήνυμα $nr από $count';

$labels['moveto']   = 'Μετακίνηση σε...';
$labels['download'] = 'Λήψη';

$labels['filename'] = 'Όνομα αρχείου';
$labels['filesize'] = 'Μέγεθος αρχείου';

$labels['preferhtml'] = 'Προτιμάται HTML';
$labels['htmlmessage'] = 'Μήνυμα HTML';
$labels['prettydate'] = 'Μορφοποιημένες ημερομηνίες';

$labels['addtoaddressbook'] = 'Προσθήκη στο βιβλίο διευθύνσεων';

// weekdays short
$labels['sun'] = 'Κυρ';
$labels['mon'] = 'Δευ';
$labels['tue'] = 'Τρι';
$labels['wed'] = 'Τετ';
$labels['thu'] = 'Πεμ';
$labels['fri'] = 'Παρ';
$labels['sat'] = 'Σαβ';

// weekdays long
$labels['sunday']    = 'Κυριακή';
$labels['monday']    = 'Δευτέρα';
$labels['tuesday']   = 'Τρίτη';
$labels['wednesday'] = 'Τετάρτη';
$labels['thursday']  = 'Πέμπτη';
$labels['friday']    = 'Παρασκευή';
$labels['saturday']  = 'Σάββατο';

$labels['today'] = 'Σήμερα';

// toolbar buttons
$labels['writenewmessage']  = 'Δημιουργία νέου μηνύματος';
$labels['replytomessage']   = 'Απάντηση μηνύματος';
$labels['forwardmessage']   = 'Προώθηση μηνύματος';
$labels['deletemessage']    = 'Μεταφορά του μηνύματος στον κάδο αχρήστων';
$labels['printmessage']     = 'Εκτύπωση μηνύματος';
$labels['previousmessages'] = 'Εμφάνιση προηγουμένων μηνυμάτων';
$labels['nextmessages']     = 'Εμφάνιση επομένων μηνυμάτων';
$labels['backtolist']       = 'Επιστροφή στη λίστα μηνυμάτων';
$labels['viewsource']       = 'Προβολή προέλευσης';

$labels['select'] = 'Επιλογή';
$labels['all'] = 'Όλα';
$labels['none'] = 'Κανένα';
$labels['unread'] = 'Αδιάβαστα';

// message compose
$labels['compose']  = 'Σύνθεση μηνύματος';
$labels['sendmessage']  = 'Αποστολή του μηνύματος τώρα';
$labels['addattachment']  = 'Επισύναψη αρχείου';
$labels['attachments'] = 'Συνημμένα';
$labels['upload'] = 'Αποστολή';
$labels['close']  = 'Κλείσιμο';

$labels['low']     = 'Χαμηλή';
$labels['lowest']  = 'Χαμηλότατη';
$labels['normal']  = 'Κανονική';
$labels['high']    = 'Υψηλή';
$labels['highest'] = 'Ύψιστη';

$labels['showimages'] = 'Εμφάνιση εικόνων';


// address boook
$labels['name']      = 'Εμφάνιση ονόματος';
$labels['firstname'] = 'Όνομα';
$labels['surname']   = 'Επώνυμο';
$labels['email']     = 'Διεύθυνση e-mail';

$labels['addcontact'] = 'Προσθήκη νέας επαφής';
$labels['editcontact'] = 'Επεξεργασία επαφής';

$labels['edit']   = 'Επεξεργασία';
$labels['cancel'] = '’κυρο';
$labels['save']   = 'Αποθήκευση';
$labels['delete'] = 'Διαγραφή';

$labels['newcontact']     = 'Δημιουργία νέας κάρτας επαφής';
$labels['deletecontact']  = 'Διαγραφή επιλεγμένων επαφών';
$labels['composeto']      = 'Σύνθεση μηνύματος προς';
$labels['contactsfromto'] = 'Επαφές $from έως $to από $count';
$labels['print']          = 'Εκτύπωση';
$labels['export']         = 'Εξαγωγή';


// settings
$labels['settingsfor']  = 'Ρυθμίσεις για';

$labels['preferences']  = 'Προτιμήσεις';
$labels['userpreferences']  = 'Προτιμήσεις χρήστη';
$labels['editpreferences']  = 'Επεξεργασία προτιμήσεων χρήστη';

$labels['identities']  = 'Ταυτότητες';
$labels['manageidentities']  = 'Διαχείριση ταυτοτήτων αυτού του λογαριασμού';
$labels['newidentity']  = 'Νέα ταυτότητα';

$labels['newitem']  = 'Νέο αντικείμενο';
$labels['edititem']  = 'Επεξεργασία αντικειμένου';

$labels['setdefault']  = 'Ορισμός προεπιλογών';
$labels['language']  = 'Γλώσσα';
$labels['timezone']  = 'Ζώνη ώρας';
$labels['pagesize']  = 'Γραμμές ανά σελίδα';

$labels['folders']  = 'Φάκελοι';
$labels['foldername']  = 'Όνομα φακέλου';
$labels['subscribed']  = 'Εγγεγραμμένοι φάκελοι';
$labels['create']  = 'Δημιουργία';
$labels['createfolder']  = 'Δημιουργία νέου φακέλου';
$labels['deletefolder']  = 'Διαγραφή φακέλου';
$labels['managefolders']  = 'Διαχείριση φακέλων';


?>
<?php

/*

 +-----------------------------------------------------------------------+
 | language/el/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Jim Deves <jim@cobaltblue.gr>                                 |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Καλώς ήρθατε στο $product';
$labels['username']  = 'Όνομα Χρήστη';
$labels['password']  = 'Κωδικός Πρόσβασης';
$labels['server']    = 'Διακομιστής';
$labels['login']     = 'Είσοδος';

// taskbar
$labels['logout']   = 'Έξοδος';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Προσωπικές Ρυθμίσεις';
$labels['addressbook'] = 'Βιβλίο Διευθύνσεων';

// mailbox names
$labels['inbox']  = 'Εισερχόμενα';
$labels['sent']   = 'Απεσταλμένα';
$labels['trash']  = 'Κάδος';
$labels['drafts'] = 'Πρόχειρα';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Θέμα';
$labels['from']    = 'Αποστολέας';
$labels['to']      = 'Παραλήπτης';
$labels['cc']      = 'Αντίγραφο';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Απάντηση σε';
$labels['date']    = 'Ημ/νία';
$labels['size']    = 'Μέγεθος';
$labels['priority'] = 'Πρωτεραιότητα';
$labels['organization'] = 'Οργανισμός';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Φάκελοι';
$labels['messagesfromto'] = 'Μηνύματα $from από $to έως $count';
$labels['messagenrof'] = 'Μήνυμα $nr από $count';

$labels['moveto']   = 'μετακίνηση σε...';
$labels['download'] = 'κατέβασμα';

$labels['filename'] = 'Όνομα αρχείου';
$labels['filesize'] = 'Μέγεθος Αρχείου';

$labels['preferhtml'] = 'Προτίμηση HTML';
$labels['htmlmessage'] = 'Μήνυμα HTML';
$labels['prettydate'] = 'Όμορφη ημ/νία';

$labels['addtoaddressbook'] = 'Προσθήκη στο Βιβλίο Διευθύνσεων';

// weekdays short
$labels['sun'] = 'Κυρ';
$labels['mon'] = 'Δευτ';
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
$labels['checkmail']        = 'Έλεγχος για νέα μηνύματα';
$labels['writenewmessage']  = 'Δημιουργία νέου μηνύματος';
$labels['replytomessage']   = 'Απάντηση';
$labels['replytoallmessage'] = 'Απάντηση στον αποστολέα και όλους τους παραλήπτες';
$labels['forwardmessage']   = 'Προώθηση μηνύματος';
$labels['deletemessage']    = 'Μετακίνηση στον Κάδο';
$labels['printmessage']     = 'Εκτύπωση';
$labels['previousmessages'] = 'Εμφάνιση προηγούμενης ομάδας μηνυμάτων';
$labels['nextmessages']     = 'Εμφάνιση επόμενης ομάδας μηνυμάτων';
$labels['backtolist']       = 'Επιστροφή στη λίστα μηνυμάτων';
$labels['viewsource']       = 'Προβολή πηγαίου κώδικα';

$labels['select'] = 'Επιλογή';
$labels['all'] = 'Όλα';
$labels['none'] = 'Κανένα';
$labels['unread'] = 'Μη αναγνωσμένα';

$labels['compact'] = 'Συμπίεση';
$labels['empty'] = 'Άδειασμα';
$labels['purge'] = 'Καθάρισμα';

$labels['quota'] = 'Χρήση δίσκου';
$labels['unknown']  = 'άγνωστο';
$labels['unlimited']  = 'απεριόριστο';

$labels['quicksearch']  = 'Γρήγορη Εύρεση';
$labels['resetsearch']  = 'Επαναφορά Εύρεσης';


// message compose
$labels['compose']  = 'Συγγραφή μηνύματος';
$labels['savemessage']  = 'Αποθήκευση ως πρόχειρου';
$labels['sendmessage']  = 'Αποστολή μηνύματος τώρα';
$labels['addattachment']  = 'Επισύναψη αρχείου';
$labels['charset']  = 'Charset';
$labels['returnreceipt'] = 'Αναφορά Παράδοσης';

$labels['checkspelling'] = 'Έλεγχος ορθογραφίας';
$labels['resumeediting'] = 'Συνέχιση επεξεργασίας';
$labels['revertto'] = 'Επαναφορά σε';

$labels['attachments'] = 'Επισυνημένα';
$labels['upload'] = 'Ανέβασμα';
$labels['close']  = 'Κλείσιμο';

$labels['low']     = 'Χαμηλή';
$labels['lowest']  = 'Χαμηλότερη';
$labels['normal']  = 'Κανονική';
$labels['high']    = 'Υψηλή';
$labels['highest'] = 'Υψηλότερη';

$labels['nosubject']  = '(κανένα θέμα)';
$labels['showimages'] = 'Εμφάνιση εικόνων';


// address boook
$labels['name']      = 'Εμφανιζόμενο όνομα';
$labels['firstname'] = 'Όνομα';
$labels['surname']   = 'Επίθετο';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Προσθήκη επαφής';
$labels['editcontact'] = 'Επεξεργασία επαφής';

$labels['edit']   = 'Επεξεργασία';
$labels['cancel'] = 'Ακύρωση';
$labels['save']   = 'Αποθήκευση';
$labels['delete'] = 'Διαγραφή';

$labels['newcontact']     = 'Δημιουργία νέας καρτέλας επαφής';
$labels['addcontact']     = 'Προσθήκη επιλεγμένης επαφής στο Βιβλίο Διευθύνσεων';
$labels['deletecontact']  = 'Διαγραφή επιλεγμένων επαφών';
$labels['composeto']      = 'Σύνθεση μηνύματος προς';
$labels['contactsfromto'] = 'Επαφή $from από $to έως $count';
$labels['print']          = 'Εκτύπωση';
$labels['export']         = 'Εξαγωγή';

$labels['previouspage']   = 'Εμφάνιση προηγούμενης σελίδας';
$labels['nextpage']       = 'Εμφάνιση επόμενης σελίδας';

// LDAP search
$labels['ldapsearch'] = 'LDAP directory search';

$labels['ldappublicsearchname']    = 'Contact name';
$labels['ldappublicsearchtype'] = 'Exact match?';
$labels['ldappublicserverselect'] = 'Select servers';
$labels['ldappublicsearchfield'] = 'Search on';
$labels['ldappublicsearchform'] = 'Look for a contact';
$labels['ldappublicsearch'] = 'Search';


// settings
$labels['settingsfor']  = 'Ρυθμίσεις για';

$labels['preferences']  = 'Προτιμήσεις';
$labels['userpreferences']  = 'Προτιμήσεις χρήστη';
$labels['editpreferences']  = 'Επεξεργασία προτιμήσεων χρήστη';

$labels['identities']  = 'Ταυτότητες';
$labels['manageidentities']  = 'Διαχείρηση ταυτοτήτων για το λογαριασμό αυτό';
$labels['newidentity']  = 'Νέα ταυτότητα';

$labels['newitem']  = 'Νέο αντικείμενο';
$labels['edititem']  = 'Επεξεργασία αντικειμένου';

$labels['setdefault']  = 'Ορισμός ως προεπιλογή';
$labels['language']  = 'Γλώσσα';
$labels['timezone']  = 'Περιοχή ώρας';
$labels['pagesize']  = 'Γραμμές ανά σελίδα';
$labels['signature'] = 'Υπογραφή';
$labels['dstactive']  = 'Daylight savings';

$labels['folder']  = 'Φάκελος';
$labels['folders']  = 'Φάκελοι';
$labels['foldername']  = 'Όνομα φακέλου';
$labels['subscribed']  = 'Εγγραφή';
$labels['create']  = 'Δημιουργία';
$labels['createfolder']  = 'Δημιουργία νέου φακέλου';
$labels['rename'] = 'Μετονομασία';
$labels['renamefolder'] = 'Μετονομασία φακέλου';
$labels['deletefolder']  = 'Διαγραφή φακέλου';
$labels['managefolders']  = 'Διαχείρηση φακέλων';

$labels['sortby'] = 'Ταξινόμιση κατά';
$labels['sortasc']  = 'Αύξουσα ταξινόμιση';
$labels['sortdesc'] = 'Φθίνουσα ταξινόμιση';

?>
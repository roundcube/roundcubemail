<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
*/

$labels = array();

// login page
$labels['welcome']   = 'Καλώς ήρθατε στο $product';
$labels['username']  = 'Όνομα Χρήστη';
$labels['password']  = 'Κωδικός Πρόσβασης';
$labels['server']    = 'Εξυπηρετητής';
$labels['login']     = 'Σύνδεση';

// taskbar
$labels['logout']   = 'Αποσύνδεση';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Ρυθμίσεις';
$labels['addressbook'] = 'Επαφές';

// mailbox names
$labels['inbox']  = 'Εισερχόμενα';
$labels['drafts'] = 'Πρόχειρα';
$labels['sent']   = 'Απεσταλμένα';
$labels['trash']  = 'Κάδος Απορριμάτων';
$labels['junk']   = 'Ανεπιθύμητα';

// message listing
$labels['subject'] = 'Θέμα';
$labels['from']    = 'Αποστολέας';
$labels['sender']  = 'Αποστολέας';
$labels['to']      = 'Παραλήπτης';
$labels['cc']      = 'Κοινοποίηση';
$labels['bcc']     = 'Κρυφή κοινοποίηση';
$labels['replyto'] = 'Απάντηση στο';
$labels['followupto'] = 'Σε συνέχεια';
$labels['date']    = 'Ημερομηνία';
$labels['size']    = 'Μέγεθος';
$labels['priority'] = 'Πρoτεραιότητα';
$labels['organization'] = 'Οργανισμός';
$labels['readstatus'] = 'Κατάσταση ανάγνωσης';
$labels['listoptions'] = 'Κατάλογος επιλογών';

$labels['mailboxlist'] = 'Φάκελοι';
$labels['messagesfromto'] = 'Μηνύματα $from από $to έως $count';
$labels['threadsfromto'] = 'Θέματα $from έως $to από $count';
$labels['messagenrof'] = 'Μήνυμα $nr από $count';
$labels['fromtoshort'] = '$from – $to από $count';

$labels['copy']     = 'Αντιγραφή';
$labels['move']     = 'Μετακίνηση';
$labels['moveto']   = 'Μετακίνηση σε...';
$labels['download'] = 'Λήψη';
$labels['open']     = 'Ανοιγμα';
$labels['showattachment'] = 'Εμφάνιση';
$labels['showanyway'] = 'Εμφάνιση';

$labels['filename'] = 'Όνομα αρχείου';
$labels['filesize'] = 'Μέγεθος αρχείου';

$labels['addtoaddressbook'] = 'Προσθήκη στο βιβλίο διευθύνσεων';

// weekdays short
$labels['sun'] = 'Κυρ';
$labels['mon'] = 'Δευτ';
$labels['tue'] = 'Τρι';
$labels['wed'] = 'Τετ';
$labels['thu'] = 'Πεμπ';
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

// months short
$labels['jan']	= 'Ιαν';
$labels['feb']	= 'Φεβ';
$labels['mar']	= 'Μαρ';
$labels['apr']	= 'Απρ';
$labels['may']	= 'Μαι';
$labels['jun']	= 'Ιουν';
$labels['jul'] 	= 'Ιουλ';
$labels['aug']	= 'Αυγ';
$labels['sep']	= 'Σεπ';
$labels['oct']	= 'Οκτ';
$labels['nov']	= 'Νοε';
$labels['dec']	= 'Δεκ';

// months long
$labels['longjan']	= 'Ιανουάριος';
$labels['longfeb']	= 'Φεβρουάριος';
$labels['longmar']	= 'Μάρτιος';
$labels['longapr']	= 'Απρίλιος';
$labels['longmay']	= 'Μαι';
$labels['longjun']	= 'Ιούνιος';
$labels['longjul']	= 'Ιούλιος';
$labels['longaug']	= 'Αύγουστος';
$labels['longsep']	= 'Σεπτέμβριος';
$labels['longoct']	= 'Οκτώβριος';
$labels['longnov']	= 'Νοέμβριος';
$labels['longdec']	= 'Δεκέμβριος';

$labels['today'] = 'Σήμερα';

// toolbar buttons
$labels['refresh']          = 'Ανανέωση';
$labels['checkmail']        = 'Έλεγχος για νέα μηνύματα';
$labels['compose']          = 'Δημιουργία μηνύματος';
$labels['writenewmessage']  = 'Δημιουργία νέου μηνύματος';
$labels['reply']            = 'Απάντηση';
$labels['replytomessage']   = 'Απάντηση στον αποστολέα';
$labels['replytoallmessage'] = 'Απάντηση στη λίστα ή στον αποστολέα και όλους τους παραλήπτες';
$labels['replyall']         = 'Απάντηση όλων';
$labels['replylist']        = 'Λίστα παραληπτών απάντησης';
$labels['forward']          = 'Προώθηση';
$labels['forwardinline']    = 'Προώθηση με ενσωμάτωση';
$labels['forwardattachment'] = 'Προώθηση σαν συνημμένο';
$labels['forwardmessage']   = 'Προώθηση μηνύματος';
$labels['deletemessage']    = 'Διαγραφή μηνύματος';
$labels['movemessagetotrash'] = 'Διαγραφή μηνύματος';
$labels['printmessage']     = 'Εκτύπωση μηνύματος';
$labels['previousmessage']  = 'Εμφάνιση του προηγούμενου μηνύματος';
$labels['firstmessage']     = 'Εμφάνιση του πρώτου μηνύματος';
$labels['nextmessage']      = 'Εμφάνιση του επόμενου μηνύματος';
$labels['lastmessage']      = 'Εμφάνιση του τελευταίου μηνύματος';
$labels['backtolist']       = 'Επιστροφή στη λίστα μηνυμάτων';
$labels['viewsource']       = 'Προβολή πηγαίου κώδικα';
$labels['mark']             = 'Μαρκάρισμα';
$labels['markmessages']     = 'Μαρκάρισμα μηνυμάτων';
$labels['markread']         = 'Ως αναγνωσμένα';
$labels['markunread']       = 'Ως μη-αναγνωσμένα';
$labels['markflagged']      = 'ως με σήμανση';
$labels['markunflagged']    = 'ως Χωρίς σήμανση';
$labels['moreactions']      = 'Περισσότερες ενέργειες...';
$labels['more']             = 'Περισσότερα';
$labels['back']             = 'Επιστροφή';
$labels['options']          = 'Επιλογές';

$labels['select'] = 'Επιλέξτε';
$labels['all'] = 'Όλα';
$labels['none'] = 'Κανένα';
$labels['currpage'] = 'Τρέχουσα σελίδα';
$labels['unread'] = 'Μη αναγνωσμένο';
$labels['flagged'] = 'Σημειωμένο';
$labels['unanswered'] = 'Αναπάντητο';
$labels['withattachment'] = 'Με επισύναψη';
$labels['deleted'] = 'Διεγραμμένο';
$labels['undeleted'] = 'Μη διεγραμμένο';
$labels['invert'] = 'Αναστροφή';
$labels['filter'] = 'Φίλτρο';
$labels['list'] = 'Λίστα';
$labels['threads'] = 'Θέματα';
$labels['expand-all'] = 'Ανάπτυξη όλων';
$labels['expand-unread'] = 'Ανάπτυξη των μη αναγνωσμένων';
$labels['collapse-all'] = 'Σύμπτυξη όλων';
$labels['threaded'] = 'Θεματοποιημένα';

$labels['autoexpand_threads'] = 'Ανάπτυξη θεματοποιημένων μηνυμάτων';
$labels['do_expand'] = 'όλα τα θέματα';
$labels['expand_only_unread'] = 'μόνο με τα μη αναγνωσμένα μηνύματα';
$labels['fromto'] = 'Από/Προς';
$labels['flag'] = 'Επισήμανση';
$labels['attachment'] = 'Συνημμένο αρχείο';
$labels['nonesort'] = 'Κανένα';
$labels['sentdate'] = 'Ημερ. Αποστολής';
$labels['arrival'] = 'Ημερ. Λήψης';
$labels['asc'] = 'αύξουσα';
$labels['desc'] = 'φθίνουσα';
$labels['listcolumns'] = 'Στήλες';
$labels['listsorting'] = 'Ταξινόμηση στηλών';
$labels['listorder'] = 'Σειρά ταξινόμησης';
$labels['listmode'] = 'Προβολή σε λίστα';

$labels['folderactions'] = 'Ενέργειες στους φακέλους';
$labels['compact'] = 'Συμπίεση';
$labels['empty'] = 'Άδειασμα';

$labels['quota'] = 'Χρήση δίσκου';
$labels['unknown']  = 'άγνωστο';
$labels['unlimited']  = 'απεριόριστο';

$labels['quicksearch']  = 'Γρήγορη Αναζήτηση';
$labels['resetsearch']  = 'Επαναφορά Αναζήτησης';
$labels['searchmod']  = 'Aλλαγή αναζήτησης';
$labels['msgtext']  = 'Σε όλο το μήνυμα';
$labels['body']  = 'Σώμα';

$labels['openinextwin'] = 'Άνοιγμα σε νέο παράθυρο';
$labels['emlsave'] = 'Λήψη αρχείου (.eml)';
$labels['changeformattext'] = 'Εμφάνιση σε μορφή απλού κειμένου';
$labels['changeformathtml'] = 'Εμφάνιση σε μορφή HTML';

// message compose
$labels['editasnew']      = 'Επεξεργασία σαν νέο';
$labels['send']           = 'Αποστολή';
$labels['sendmessage']    = 'Αποστολή μηνύματος';
$labels['savemessage']    = 'Αποθήκευση ως πρόχειρο';
$labels['addattachment']  = 'Επισύναψη αρχείου';
$labels['charset']        = 'Κωδικοποίηση χαρακτήρων';
$labels['editortype']     = 'Είδος επεξεργαστή κειμένου';
$labels['returnreceipt']  = 'Αναφορά Παράδοσης';
$labels['dsn']            = 'Κατάσταση αναφοράς παράδοσης';
$labels['mailreplyintro'] = 'Στις $date, $sender έγραψε:';
$labels['originalmessage'] = 'Αρχικό μήνυμα';

$labels['editidents']    = 'Επεξεργασία ταυτοτήτων';
$labels['spellcheck']    = 'Συλλαβισμός';
$labels['checkspelling'] = 'Έλεγχος ορθογραφίας';
$labels['resumeediting'] = 'Συνέχεια επεξεργασίας';
$labels['revertto']      = 'Επαναφορά στο';

$labels['attach'] = 'Επισύναψη';
$labels['attachments'] = 'Συνημμένα';
$labels['upload'] = 'Φόρτωση';
$labels['uploadprogress'] = '$percent ($current από $total)';
$labels['close']  = 'Κλείσιμο';
$labels['messageoptions']  = 'Επιλογές μηνύματος';

$labels['low']     = 'Χαμηλή';
$labels['lowest']  = 'Χαμηλότερη';
$labels['normal']  = 'Κανονική';
$labels['high']    = 'Υψηλή';
$labels['highest'] = 'Μέγιστη δυνατή';

$labels['nosubject']  = '(χωρίς θέμα)';
$labels['showimages'] = 'Προβολή εικόνων';
$labels['alwaysshow'] = 'Να προβάλλονται πάντα οι εικόνες που αποστέλλονται από τον $sender';
$labels['isdraft']    = 'Αυτό το μήνυμα έχει σημανθεί σαν πρόχειρο';
$labels['andnmore']   = '$nr περισσότερα...';
$labels['togglemoreheaders'] = 'Εμφάνιση περισσότερων κεφαλίδων';
$labels['togglefullheaders'] = 'Εμφάνιση ακατέργαστων κεφαλίδων μηνύματος';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Απλό κείμενο';
$labels['savesentmessagein'] = 'Αποθήκευση απεσταλμένου στο φάκελο';
$labels['dontsave'] = 'να μην αποθηκευθεί';
$labels['maxuploadsize'] = 'Το μέγιστο επιτρεπόμενο μέγεθος αρχείου είναι $size';

$labels['addcc'] = 'Προσθήκη Παραλήπτη Κοινοποίησης';
$labels['addbcc'] = 'Προσθήκη Παραλήπτη Κρυφής Κοινοποίησης';
$labels['addreplyto'] = 'Προσθήκη Απάντησης-Στο';
$labels['addfollowupto'] = 'Προσθήκη Σε συνέχεια του';

// mdn
$labels['mdnrequest'] = 'Ο αποστολέας αυτού του μηνύματος, έχει ζητήσει να ειδοποιηθεί όταν θα το διαβάσετε. Θέλετε να επιτρέψετε την ενημέρωσή του;';
$labels['receiptread'] = 'Αναφορά παράδοσης (διαβάστε την)';
$labels['yourmessage'] = 'Αυτό είναι μια αναφορά παράδοσης για το μήνυμά σας';
$labels['receiptnote'] = 'Σημείωση: Αυτή η αναφορά επιβεβαιώνει μόνο ότι το μήνυμά σας εμφανίστηκε στον υπολογιστή του παραλήπτη. Δεν επιβεβαιώνει ότι ο παραλήπτης έχει διαβάσει ή έχει κατανοήσει το περιεχόμενό του.';

// address boook
$labels['name']         = 'Εμφανιζόμενο όνομα';
$labels['firstname']    = 'Όνομα';
$labels['surname']      = 'Επώνυμο';
$labels['middlename']   = 'Πατρώνυμο';
$labels['nameprefix']   = 'Πρόθεμα';
$labels['namesuffix']   = 'Κατάληξη';
$labels['nickname']     = 'Ψευδώνυμο';
$labels['jobtitle']     = 'Τίτλος εργασίας';
$labels['department']   = 'Τμήμα';
$labels['gender']       = 'Φύλο';
$labels['maidenname']   = 'Πατρώνυμο';
$labels['email']        = 'E-Mail';
$labels['phone']        = 'Τηλέφωνο';
$labels['address']      = 'Διεύθυνση';
$labels['street']       = 'Οδός';
$labels['locality']     = 'Πόλη';
$labels['zipcode']      = 'ΤΚ';
$labels['region']       = 'Νομός';
$labels['country']      = 'Χώρα';
$labels['birthday']     = 'Ημερ/νία Γέννησης';
$labels['anniversary']  = 'Επέτειος';
$labels['website']      = 'Ιστοσελίδα';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Σημειώσεις';
$labels['male']   = 'άνδρας';
$labels['female'] = 'γυναίκα';
$labels['manager'] = 'Υπεύθυνος';
$labels['assistant'] = 'Υπάλληλος';
$labels['spouse'] = 'Σύζυγος';
$labels['allfields'] = 'Όλα τα πεδία';
$labels['search'] = 'Αναζήτηση';
$labels['advsearch'] = 'Προηγμένη Αναζήτηση';
$labels['advanced'] = 'Προχωρημένες ρυθμίσεις';
$labels['other'] = 'Άλλα';

$labels['typehome']   = 'Οικία';
$labels['typework']   = 'Εργασία';
$labels['typeother']  = 'Άλλα';
$labels['typemobile']  = 'Κινητό';
$labels['typemain']  = 'Κύρια';
$labels['typehomefax']  = 'Φαξ οικίας';
$labels['typeworkfax']  = 'Φαξ εργασίας';
$labels['typecar']  = 'Αυτοκίνητο';
$labels['typepager']  = 'Βομβητής';
$labels['typevideo']  = 'Βίντεο';
$labels['typeassistant']  = 'Υπάλληλος';
$labels['typehomepage']  = 'Αρχική Σελίδα';
$labels['typeblog'] = 'Ιστολόγιο';
$labels['typeprofile'] = 'Προφίλ';

$labels['addfield'] = 'Προσθήκη πεδίου...';
$labels['addcontact'] = 'Προσθήκη νέας επαφής';
$labels['editcontact'] = 'Επεξεργασία επαφής';
$labels['contacts'] = 'Επαφές';
$labels['contactproperties'] = 'Ιδιότητες επαφών';
$labels['personalinfo'] = 'Προσωπικές πληροφορίες';

$labels['edit']   = 'Επεξεργασία';
$labels['cancel'] = 'Ακύρωση';
$labels['save']   = 'Αποθήκευση';
$labels['delete'] = 'Διαγραφή';
$labels['rename'] = 'Μετονομασία';
$labels['addphoto'] = 'Προσθήκη';
$labels['replacephoto'] = 'Αντικατάσταση';
$labels['uploadphoto'] = 'Φόρτωση εικόνας';

$labels['newcontact']     = 'Δημιουργία νέας κάρτας επαφής';
$labels['deletecontact']  = 'Διαγραφή επιλεγμένων επαφών';
$labels['composeto']      = 'Σύνθεση μηνύματος προς';
$labels['contactsfromto'] = 'Επαφή $from από $to έως $count';
$labels['print']          = 'Εκτύπωση';
$labels['export']         = 'Εξαγωγή';
$labels['exportall']      = 'Εξαγωγή όλων';
$labels['exportsel']      = 'Εξαγωγή επιλεγμένων';
$labels['exportvcards']   = 'Εξαγωγή επαφών σε vCard';
$labels['newcontactgroup'] = 'Δημιουργία νέας Ομάδας επαφών';
$labels['grouprename']    = 'Μετονομασία ομάδας';
$labels['groupdelete']    = 'Διαγραφή ομάδας';
$labels['groupremoveselected'] = 'Αφαίρεση επιλεγμένων επαφών από την ομάδα';

$labels['previouspage']   = 'Εμφάνιση προηγούμενης σελίδας';
$labels['firstpage']      = 'Εμφάνιση της πρώτης σελίδας';
$labels['nextpage']       = 'Εμφάνιση επόμενης σελίδας';
$labels['lastpage']       = 'Εμφάνιση της τελευταίας σελίδας';

$labels['group'] = 'Ομάδα';
$labels['groups'] = 'Ομάδες';
$labels['personaladrbook'] = 'Προσωπικές Διυθύνσεις';

$labels['searchsave'] = 'Αποθήκευση αναζήτησης';
$labels['searchdelete'] = 'Διαγραφή αποθηκευμένης αναζήτησης';

$labels['import'] = 'Εισαγωγή';
$labels['importcontacts'] = 'Εισαγωγή επαφών';
$labels['importfromfile'] = 'Εισαγωγή από το αρχείο:';
$labels['importtarget'] = 'Προσθήκη νέων επαφών στο βιβλίο διευθύνσεων:';
$labels['importreplace'] = 'Αντικατάσταση όλου του βιβλίου διευθύνσεων';
$labels['importdesc'] = 'Μπορείτε να ανεβάσετε επαφές από ένα υπάρχον βιβλίο διευθύνσεων.<br/>Υποστηρίζεται η εισαγωγή διευθύνσεων από αρχεία τύπου <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> ή CSV (διαχωρισμένα με κόμμα).';
$labels['done'] = 'Ολοκληρώθηκε';

// settings
$labels['settingsfor'] = 'Ρυθμίσεις για';
$labels['about'] = 'Σχετικά';
$labels['preferences'] = 'Προτιμήσεις';
$labels['userpreferences'] = 'Προτιμήσεις χρήστη';
$labels['editpreferences'] = 'Επεξεργασία προτιμήσεων χρήστη';

$labels['identities'] = 'Ταυτότητες';
$labels['manageidentities'] = 'Διαχείριση ταυτοτήτων για αυτόν το λογαριασμό';
$labels['newidentity'] = 'Νέα ταυτότητα';

$labels['newitem'] = 'Νέο αντικείμενο';
$labels['edititem'] = 'Επεξεργασία αντικειμένου';

$labels['preferhtml'] = 'Προβολή σε HTML';
$labels['defaultcharset'] = 'Προεπιλεγμένη κωδικοποίηση χαρακτήρων';
$labels['htmlmessage'] = 'Μήνυμα σε μορφή HTML';
$labels['messagepart'] = 'Μέρος';
$labels['digitalsig'] = 'Ψηφιακή Υπογραφή';
$labels['dateformat'] = 'Μορφή ημερομηνίας';
$labels['timeformat'] = 'Μορφή ώρας';
$labels['prettydate'] = 'Κομψή ημερ/νία';
$labels['setdefault']  = 'Ορισμός ως προεπιλογή';
$labels['autodetect']  = 'Αυτόματα';
$labels['language']  = 'Γλώσσα';
$labels['timezone']  = 'Ζώνη Ώρας';
$labels['pagesize']  = 'Γραμμές ανά σελίδα';
$labels['signature'] = 'Υπογραφή';
$labels['dstactive']  = 'Θερινή ώρα';
$labels['showinextwin'] = 'Άνοιγμα μηνύματος σε νέο παράθυρο';
$labels['composeextwin'] = 'Σύνθεση μηνύματος σε νέο παράθυρο';
$labels['htmleditor'] = 'Δημιουργία μηνύματος σε μορφή HTML';
$labels['htmlonreply'] = 'μόνο σε απάντηση μηνύματος μορφής HTML';
$labels['htmlonreplyandforward'] = 'σε προώθηση ή σε απάντηση σε HTML μήνυμα';
$labels['htmlsignature'] = 'Υπογραφή HTML';
$labels['showemail'] = 'Εμφάνιση διεύθυνσης e-mail με το εμφανιζόμενο όνομα';
$labels['previewpane'] = 'Εμφάνιση προεπισκόπησης';
$labels['skin'] = 'Θέμα εμφάνισης';
$labels['logoutclear'] = 'Άδειασμα του κάδου στην έξοδο';
$labels['logoutcompact'] = 'Συμπίεση φακέλου εισερχομένων στην έξοδο';
$labels['uisettings'] = 'Περιβάλλον χρήστη';
$labels['serversettings'] = 'Ρυθμίσεις διακομιστή';
$labels['mailboxview'] = 'Προβολή Μηνυμάτων';
$labels['mdnrequests'] = 'Όταν ο αποστολέας έχει ζητήσει αναφορά παράδοσης';
$labels['askuser'] = 'ρώτησέ με';
$labels['autosend'] = 'στείλε αυτόματα την απόδειξη παραλαβής';
$labels['autosendknown'] = 'στείλε αυτόματα αποδείξεις παραλαβής σε όσους έχω στις επαφές, για τους άλλους ρώτα με';
$labels['autosendknownignore'] = 'στείλε αυτόματα αποδείξεις παραλαβής σε όσους έχω στις επαφές, για τους άλλους καμιά ενέργεια';
$labels['ignore'] = 'αγνόησε';
$labels['readwhendeleted'] = 'Κατά τη διαγραφή του μηνύματος, μαρκάρισέ το σαν αναγνωσμένο';
$labels['flagfordeletion'] = 'Αντί να το διαγράψεις, απλά και μόνο σημείωσέ το';
$labels['skipdeleted'] = 'Να μην εμφανίζονται τα διαγραμμένα μηνύματα';
$labels['deletealways'] = 'αν η μετακίνηση στον κάδο αποτύχει, διέγραψέ τα';
$labels['deletejunk'] = 'Άμεση διαγραφή μηνυμάτων στα Ανεπιθύμητα';
$labels['showremoteimages'] = 'Να εμφανίζονται οι εικόνες που έχουν αποσταλεί σε συνδέσμους';
$labels['fromknownsenders'] = 'από γνωστούς αποστολείς';
$labels['always'] = 'πάντα';
$labels['showinlineimages'] = 'Οι συνημμένες εικόνες να προβάλλονται στο τέλος του μηνήματος';
$labels['autosavedraft']  = 'Να γίνεται αυτόματη αποθήκευση σαν πρόχειρο';
$labels['everynminutes']  = 'κάθε $n λεπτό/α';
$labels['refreshinterval']  = 'Επαναφόρτωση (έλεγχος για νέα μηνύματα, κλπ.)';
$labels['never']  = 'ποτέ';
$labels['immediately']  = 'άμεσα';
$labels['messagesdisplaying'] = 'Προβολή Μηνυμάτων';
$labels['messagescomposition'] = 'Δημιουργία Μηνυμάτων';
$labels['mimeparamfolding'] = 'Ονόματα συνημμένων αρχείων';
$labels['2231folding'] = 'Full RFC 2231 (μορφή Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (μορφή MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (άλλη μορφή)';
$labels['force7bit'] = 'Να γίνεται χρήση της κωδικοποίησης MIME για τους χαρακτήρες 8-bit';
$labels['advancedoptions'] = 'Προχωρημένες επιλογές';
$labels['focusonnewmessage'] = 'Τα νέα μηνύματα να επιλέγονται αυτόματα';
$labels['checkallfolders'] = 'Να γίνεται έλεγχος σε όλους τους φακέλους για νέα μηνύματα';
$labels['displaynext'] = 'Μετά από κάθε μετακίνηση/διαγραφή μηνήματος να εμφανίζεται το επόμενο';
$labels['defaultfont'] = 'Προεπιλεγμένη γραμματοσειρά για τα μηνύματα μορφής HTML';
$labels['mainoptions'] = 'Βασικές επιλογές';
$labels['browseroptions'] = 'Επιλογές περιηγητή';
$labels['section'] = 'Ενότητα';
$labels['maintenance'] = 'Συντήρηση';
$labels['newmessage'] = 'Νέο Μήνυμα';
$labels['signatureoptions'] = 'Επιλογές υπογραφής';
$labels['whenreplying'] = 'Στις απαντήσεις';
$labels['replyempty'] = 'χωρίς παράθεση του αρχικού μηνύματος';
$labels['replytopposting'] = 'το νέο μήνυμα να είναι πάνω από το αρχικό';
$labels['replybottomposting'] = 'το νέο μήνυμα να είναι κάτω από το αρχικό';
$labels['replyremovesignature'] = 'Όταν το μήνυμα είναι απάντηση, να αφαιρείται η υπογραφή του αρχικού μηνύματος';
$labels['autoaddsignature'] = 'Η υπογραφή να προστίθεται αυτόματα';
$labels['newmessageonly'] = 'μόνο στα νέα μηνύματα';
$labels['replyandforwardonly'] = 'μόνο στις απαντήσεις και προωθήσεις';
$labels['insertsignature'] = 'Προσθήκη υπογραφής';
$labels['previewpanemarkread']  = 'Στα μηνύματα που έγινε προεπισκόπηση να μαρκάρονται σαν αναγνωσμένα';
$labels['afternseconds']  = 'μετά από $n δευτερόλεπτα';
$labels['reqmdn'] = 'Να γίνεται πάντα αίτηση για αναφορά απάντησης';
$labels['reqdsn'] = 'Να γίνεται πάντα αίτηση για αναφορά παράδοσης';
$labels['replysamefolder'] = 'Τα μηνύματα που είναι απαντήσεις να τοποθετούνται σε ξεχωριστό φάκελο';
$labels['defaultabook'] = 'Προκαθορισμένο βιβλίο διευθύνσεων';
$labels['autocompletesingle'] = 'Να μην χρησιμοποιούνται οι εναλλακτικές διευθύνσεις κατά την αυτόματη συμπλήρωση';
$labels['listnamedisplay'] = 'Εμφάνιση επαφών ως';
$labels['spellcheckbeforesend'] = 'Να γίνεται πάντα έλεγχος ορθογραφίας πριν την αποστολή του μηνύματος';
$labels['spellcheckoptions'] = 'Επιλογές Ορθογραφικού ελέγχου';
$labels['spellcheckignoresyms'] = 'Οι λέξεις που περιέχουν σύμβολα να αγνοούνται';
$labels['spellcheckignorenums'] = 'Οι λέξεις που περιέχουν αριθμούς να αγνοούνται';
$labels['spellcheckignorecaps'] = 'Οι λέξεις γραμμένες με ΚΕΦΑΛΑΙΑ να αγνοούνται';
$labels['addtodict'] = 'Προσθήκη στο λεξικό';
$labels['mailtoprotohandler'] = 'Καταχώρηση διαχείρισης πρωτοκόλλου δεσμών mailto:';
$labels['forwardmode'] = 'Προώθηση μηνυμάτων';
$labels['inline'] = 'με εσνωμάτωση';
$labels['asattachment'] = 'σαν επισύναψη';

$labels['folder']  = 'Φάκελος';
$labels['folders']  = 'Φάκελοι';
$labels['foldername']  = 'Όνομα φακέλου';
$labels['subscribed']  = 'Εγγεγραμμένος';
$labels['messagecount'] = 'Μηνύματα';
$labels['create']  = 'Δημιουργία';
$labels['createfolder']  = 'Δημιουργία νέου φακέλου';
$labels['managefolders']  = 'Διαχείριση φακέλων';
$labels['specialfolders'] = 'Ειδικοί Φάκελοι';
$labels['properties'] = 'Ιδιότητες';
$labels['folderproperties'] = 'Ιδιότητες φακέλου';
$labels['parentfolder'] = 'Γονικός φάκελος';
$labels['location'] = 'Τοποθεσία';
$labels['info'] = 'Πληροφορίες';
$labels['getfoldersize'] = 'Κάντε κλικ εδώ για να δείτε το μέγεθος του φακέλου';
$labels['changesubscription'] = 'Κάντε κλικ εδώ για να αλλάξετε τη συνδρομή σας';
$labels['foldertype'] = 'Τύπος φακέλου';
$labels['personalfolder']  = 'Προσωπικός φάκελος';
$labels['otherfolder']  = 'Φάκελοι άλλων χρηστών';
$labels['sharedfolder']  = 'Κοινόχρηστος φάκελος';

$labels['sortby'] = 'Ταξινόμηση κατά';
$labels['sortasc']  = 'Αύξουσα ταξινόμηση';
$labels['sortdesc'] = 'Φθίνουσα ταξινόμηση';
$labels['undo'] = 'Αναίρεση';

$labels['installedplugins'] = 'Εγκατεστημένα πρόσθετα';
$labels['plugin'] = 'Πρόσθετο';
$labels['version'] = 'Έκδοση';
$labels['source'] = 'Προέλευση';
$labels['license'] = 'Άδεια χρήσης';
$labels['support'] = 'Υποστήριξη';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'Αγγλικά';
$labels['westerneuropean'] = 'Δυτικής Ευρώπης';
$labels['easterneuropean'] = 'Ανατολικής Ευρώπης';
$labels['southeasterneuropean'] = 'Νοτιο-Ανατολικής Ευρώπης';
$labels['baltic'] = 'Βαλτικής';
$labels['cyrillic'] = 'Κυριλλικά';
$labels['arabic'] = 'Αραβικά';
$labels['greek'] = 'Ελληνικά';
$labels['hebrew'] = 'Εβραϊκά';
$labels['turkish'] = 'Τουρκικά';
$labels['nordic'] = 'Σκανδιναβικά';
$labels['thai'] = 'Ταϊλανδέζικα';
$labels['celtic'] = 'Κελτικά';
$labels['vietnamese'] = 'Βιετναμέζικα';
$labels['japanese'] = 'Ιαπωνικά';
$labels['korean'] = 'Κορεατικά';
$labels['chinese'] = 'Κινεζικά';

?>

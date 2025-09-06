<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Password plugin            |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/

$labels['changepasswd']  = 'Αλλαγή κωδικού';
$labels['curpasswd']  = 'Τρέχων κωδικός πρόσβασης:';
$labels['newpasswd']  = 'Νέος κωδικός πρόσβασης:';
$labels['confpasswd']  = 'Επιβέβαιωση κωδικού πρόσβασης:';
$messages['nopassword'] = 'Εισάγετε ένα νέο κωδικό.';
$messages['nocurpassword'] = 'Εισάγετε τον τρέχοντα κωδικό σας.';
$messages['passwordincorrect'] = 'Ο τρέχων κωδικός είναι λάθος.';
$messages['passwordinconsistency'] = 'Οι κωδικοί πρόσβασης δεν ταιριάζουν, προσπαθήστε ξανά.';
$messages['crypterror'] = 'Δεν μπόρεσε να αποθηκευτεί ο νέος κωδικός πρόσβασης. Η λειτουργία κρυπτογράφησης λείπει.';
$messages['connecterror'] = 'Δεν μπορεσε να αποθηκευτεί ο νέος κωδικός πρόσβασης. Σφάλμα σύνδεσης.';
$messages['internalerror'] = 'Δεν μπορεσε να αποθηκευτεί ο νέος κωδικός πρόσβασης. ';
$messages['passwordshort'] = 'Ο κωδικός πρόσβασης πρέπει να είναι τουλάχιστον $length χαρακτήρων.';
$messages['passwordweak'] = 'Ο κωδικός πρόσβασης πρέπει να περιλαμβάνει τουλάχιστον έναν αριθμό και ένα σημείο στίξης. ';
$messages['passwordtooweak'] = 'Το συνθηματικό είναι πολύ ασθενές.';
$messages['passwordnoseq'] = 'Το συνθηματικό δεν πρέπει να είναι μια ακολουθία όπως 123456 ή QWERTY.';
$messages['passwordnocommon'] = 'Το συνθηματικό δεν πρέπει να είναι μια κοινή λέξη ή όνομα.';
$messages['passwordforbidden'] = 'Ο κωδικός πρόσβασης περιέχει μη επιτρεπτούς χαρακτήρες. ';
$messages['firstloginchange'] = 'Αυτή είναι η πρώτη σας είσοδος. Παρακαλώ αλλάξτε το συνθηματικό σας.';
$messages['disablednotice'] = 'Το σύστημα βρίσκεται υπό συντήρηση και η αλλαγή του κωδικού πρόσβασης δεν είναι δυνατή αυτή τη στιγμή. Τα πάντα θα πρέπει να επανέλθουν σε κανονική λειτουργία σύντομα. Ζητούμε συγγνώμη για την όποια ταλαιπωρία.';
$messages['passwinhistory'] = 'Αυτό το συνθηματικό έχει ήδη χρησιμοποιηθεί.';
$messages['samepasswd'] = 'Το νέο συνθηματικό πρέπει να είναι διαφορετικό από το παλιό.';
$messages['passwdexpirewarning'] = 'Προσοχή! Το συνθηματικό σας θα λήξει σύντομα, αλλάξτε το πριν από $expirationdatetime.';
$messages['passwdexpired'] = 'Το συνθηματικό σας έληξε, πρέπει να το αλλάξετε τώρα!';
$messages['passwdconstraintviolation'] = 'Παραβίαση περιορισμών συνθηματικού. Το συνθηματικό είναι πιθανώς πολύ ασθενές.';
$messages['pwned_mustnotbedisclosed'] = 'Ο κωδικός πρόσβασης δεν πρέπει να είναι <a href="$href" target="_blank">ευρέως γνωστός</a>.';
$messages['pwned_isdisclosed'] = 'Αυτός ο κωδικός είναι ευρέως γνωστός.';
$messages['pwned_fetcherror'] = 'Αποτυχία επαλήθευσης της δύναμης του κωδικού πρόσβασης.';

$labels['hestia_err_policy_short'] = 'Πολύ μικρός κωδικός πρόσβασης (ελάχιστο μήκος: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Το τελικό σημείο Hestia δεν έχει ρυθμιστεί ή είναι άκυρο.';
$labels['hestia_err_identity'] = 'Δεν είναι δυνατή η ανάλυση του ονόματος χρήστη σε local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Δεν είναι δυνατός ο προσδιορισμός του ιδιοκτήτη του τομέα για το %domain.';
$labels['hestia_err_unknown'] = 'Άγνωστο σφάλμα από το API της Hestia.';
$labels['hestia_err_default'] = 'Η Εστία επέστρεψε κωδικό σφάλματος %code.';
$labels['hestia_err_1'] = 'Δεν παρέχονται αρκετά επιχειρήματα.';
$labels['hestia_err_2'] = 'Μη έγκυρο αντικείμενο ή όρισμα.';
$labels['hestia_err_3'] = 'Το αντικείμενο δεν υπάρχει (δεν βρέθηκε γραμματοκιβώτιο ή τομέας).';
$labels['hestia_err_4'] = 'Το αντικείμενο υπάρχει ήδη.';
$labels['hestia_err_5'] = 'Το αντικείμενο έχει ήδη ανασταλεί.';
$labels['hestia_err_6'] = 'Το αντικείμενο δεν έχει ήδη ανασταλεί.';
$labels['hestia_err_7'] = 'Αντικείμενο σε χρήση- δεν μπορεί να τροποποιηθεί.';
$labels['hestia_err_8'] = 'Η λειτουργία εμποδίζεται από τα όρια του πακέτου.';
$labels['hestia_err_9'] = 'Λάθος ή άκυρος κωδικός πρόσβασης.';
$labels['hestia_err_10'] = 'Άδεια απορριφθεί από το Hestia (η ταυτότητα API δεν έχει δικαιώματα ή η IP δεν επιτρέπεται).';
$labels['hestia_err_11'] = 'Λειτουργία απενεργοποιημένη από την πολιτική συστήματος.';
$labels['hestia_err_12'] = 'Σφάλμα ανάλυσης.';
$labels['hestia_err_13'] = 'Σφάλμα δίσκου.';
$labels['hestia_err_14'] = 'Το φορτίο του διακομιστή είναι πολύ υψηλό.';
$labels['hestia_err_15'] = 'Σφάλμα σύνδεσης.';
$labels['hestia_err_16'] = 'Σφάλμα FTP.';
$labels['hestia_err_17'] = 'Σφάλμα βάσης δεδομένων.';
$labels['hestia_err_18'] = 'Σφάλμα RRD.';
$labels['hestia_err_19'] = 'Σφάλμα ενημέρωσης.';
$labels['hestia_err_20'] = 'Σφάλμα επανεκκίνησης.';
?>

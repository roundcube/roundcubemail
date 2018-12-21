<?php

/*
 +-----------------------------------------------------------------------+
 | plugins/password/localization/<lang>.inc                              |
 |                                                                       |
 | Localization file of the Roundcube Webmail Password plugin            |
 | Copyright (C) 2012-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
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
